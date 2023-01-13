<?php


namespace App\Service;


use App\Entity\Message;
use App\Entity\MessageFile;
use App\Entity\User;
use App\Repository\DiscountVoucherRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use ArtoxLab\Bundle\SmsBundle\Service\ProviderManager;
use ArtoxLab\Bundle\SmsBundle\Sms\Sms;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Factory\QrCodeFactory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UtilsService
{
    private $discountVoucherRepository;
    private $router;
    private $factory;
    private $em;
    private $kernel;
    private $userRepositoty;
    private $orderRepositoty;
    private $productRepository;
    private $providerManager;
    private $messaging;
    private $texter;

    public function __construct(DiscountVoucherRepository $discountVoucherRepository,
                                UrlGeneratorInterface $router,
                                QrCodeFactory $factory,
                                EntityManagerInterface $em,
                                KernelInterface $kernel,
                                UserRepository $userRepository,
                                OrderRepository $orderRepository,
                                ProductRepository $productRepository,
                                ProviderManager $providerManager,
                                Messaging $messaging,
                                TexterInterface $texter)
    {
        $this->discountVoucherRepository = $discountVoucherRepository;
        $this->router = $router;
        $this->factory = $factory;
        $this->em = $em;
        $this->kernel = $kernel;
        $this->userRepositoty = $userRepository;
        $this->orderRepositoty = $orderRepository;
        $this->productRepository = $productRepository;
        $this->providerManager = $providerManager;
        $this->messaging = $messaging;
        $this->texter = $texter;
    }

    public function generateRandomVoucherCode(int $length)
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $res = "";
        for ($i = 0; $i < $length; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $found = $this->discountVoucherRepository->findOneByCode($res);
        if (!is_null($found)) {
            $this->generateRandomVoucherCode($length);
        }
        return $res;
    }

    public function generateQrCode(MessageFile $file)
    {
        $route = $this->router->generate('show_message_file', ['id' => $file->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $code = $this->factory->create($route);
        $codeName = uniqid($file->getId()) . '.png';
        $codeName = $file->setQrCode($codeName);
        $this->em->persist($file);
        $this->em->flush();
        $code->writeFile($this->kernel->getProjectDir() . '/public/qrCodes/' . $codeName);
    }

    public function getUsersChartData()
    {
        $lastMonthDays = [];
        $lastYearMonths = [];

        for ($i = 0; $i <= 30; $i++) {
            $now = new \DateTime();
            $lastMonthDays[$now->sub(date_interval_create_from_date_string($i . ' days'))
                ->format('d F Y')] = 0;
        }
        for ($i = 0; $i <= 11; $i++) {
            $now = new \DateTime();
            $lastYearMonths[$now->sub(date_interval_create_from_date_string($i . ' months'))
                ->format('F Y')] = 0;
        }
        $lastMonthDaysUsers = $this->userRepositoty->countUsersPerDate($lastMonthDays);
        $lastYearMonthsUsers = $this->userRepositoty->countUsersPerMonths($lastYearMonths);

        foreach ($lastMonthDaysUsers as $el) {
            $lastMonthDays[$el['day']] = $el['count'];
        }
        $lastWeekDays = array_slice($lastMonthDays, 0, 7);

        foreach ($lastYearMonthsUsers as $el) {
            $lastYearMonths[$el['month']] = $el['count'];
        }

        return [
            'week' => $lastWeekDays,
            'month' => $lastMonthDays,
            'year' => $lastYearMonths
        ];
    }

    public function getOrdersChartData()
    {
        $lastMonthDays = [];
        $lastYearMonths = [];

        for ($i = 0; $i <= 30; $i++) {
            $now = new \DateTime();
            $lastMonthDays[] = $now->sub(date_interval_create_from_date_string($i . ' days'))
                ->format('d F Y');
        }

        $lastWeekDays = array_slice($lastMonthDays, 0, 7);

        for ($i = 0; $i <= 11; $i++) {
            $now = new \DateTime();
            $lastYearMonths[] = $now->sub(date_interval_create_from_date_string($i . ' months'))
                ->format('F Y');
        }

        $lastWeekDaysOrders = $this->orderRepositoty->countOrdersPerDate($lastWeekDays);
        $lastMonthDaysOrders = $this->orderRepositoty->countOrdersPerDate($lastMonthDays);
        $lastYearMonthsOrders = $this->orderRepositoty->countOrdersPerMonths($lastYearMonths);


        foreach ($lastMonthDaysOrders as $el) {
            $monthOrders[$el['mapName']] = $el['count'];
        }

        foreach ($lastWeekDaysOrders as $el) {
            $weekOrders[$el['mapName']] = $el['count'];
        }

        foreach ($lastYearMonthsOrders as $el) {
            $yeatOrders[$el['mapName']] = $el['count'];
        }

        return [
            'week' => $weekOrders ?? [],
            'month' => $monthOrders ?? [],
            'year' => $yeatOrders ?? []
        ];
    }

    public function getDashboardData()
    {
        $lastMonthDays = [];

        for ($i = 0; $i <= 30; $i++) {
            $now = new \DateTime();
            $lastMonthDays[] = $now->sub(date_interval_create_from_date_string($i . ' days'))
                ->format('d F Y');
        }
        $lastWeekDays = array_slice($lastMonthDays, 0, 7);

        $clients = [
            'total' => $this->userRepositoty->countClients(),
            'week' => $this->userRepositoty->countClients($lastWeekDays),
            'month' => $this->userRepositoty->countClients($lastMonthDays),
        ];

        $orders = [
            'total' => $this->orderRepositoty->countOrders(),
            'week' => $this->orderRepositoty->countOrders($lastWeekDays),
            'month' => $this->orderRepositoty->countOrders($lastMonthDays),
        ];

        $avgSales = [
            'total' => $this->orderRepositoty->countOrdersAverageSales(),
            'week' => $this->orderRepositoty->countOrdersAverageSales($lastWeekDays),
            'month' => $this->orderRepositoty->countOrdersAverageSales($lastMonthDays),
        ];

        $nbShops = $this->userRepositoty->count(['role' => User::USER_ROLES['shop']]);
        $nbProducts = $this->productRepository->count([]);
        $totalIncome = $this->orderRepositoty->getTotalIncome();

        return compact('clients', 'orders', 'avgSales', 'nbShops', 'nbProducts', 'totalIncome');
    }

    public function getDashboardDataShop($user)
    {
        $lastMonthDays = [];

        for ($i = 0; $i <= 30; $i++) {
            $now = new \DateTime();
            $lastMonthDays[] = $now->sub(date_interval_create_from_date_string($i . ' days'))
                ->format('d F Y');
        }
        $lastWeekDays = array_slice($lastMonthDays, 0, 7);

        $orders = [
            'total' => $this->orderRepositoty->countOrdersByShop([], $user),
            'week' => $this->orderRepositoty->countOrdersByShop($lastWeekDays, $user),
            'month' => $this->orderRepositoty->countOrdersByShop($lastMonthDays, $user),
        ];

        $avgSales = [
            'total' => $this->orderRepositoty->countOrdersAverageSalesByShop([], $user),
            'week' => $this->orderRepositoty->countOrdersAverageSalesByShop($lastWeekDays, $user),
            'month' => $this->orderRepositoty->countOrdersAverageSalesByShop($lastMonthDays, $user),
        ];

        $totalIncome = $this->orderRepositoty->getTotalIncomeByShop($user);

        return compact('orders', 'avgSales', 'totalIncome');
    }

    public function generateLastWeekDays()
    {
        $today = new \DateTime();
        $currentWeek = $today->format('W');
        $currentDay = $today->format('N');

        for ($i = 1; $i <= 6 + intval($currentDay); $i++) {
            $now = new \DateTime();

            $date = $now->sub(date_interval_create_from_date_string($i . ' days'));
            if ($date->format('W') == $currentWeek) {
                if ($date->format('N') < 5) {
                    $dates[] = $date->format('d F Y');
                }
            } else {
                if ($date->format('N') > 4) {
                    $dates[] = $date->format('d F Y');
                }
            }
        }
        $dates = array_reverse($dates);

        for ($i = 0; $i <= 7 - intval($currentDay); $i++) {
            $now = new \DateTime();

            $date = $now->add(date_interval_create_from_date_string($i . ' days'));
            if ($date->format('W') == $currentWeek) {
                if ($date->format('N') < 4) {
                    $dates[] = $date->format('d F Y');
                }
            } else {
                if ($date->format('N') >= 5) {
                    $dates[] = $date->format('d F Y');
                }
            }
        }

        return $dates;
    }

    public function sendVerificationCode($phone)
    {
        try {
            $verificationCode = $this->generateRandomDigitsCode(4);
            $message = 'Alwarda.sa : Your verification code is : ' . $verificationCode;
            $sms = new SmsMessage($phone, $message);
            $this->texter->send($sms);
            return ['success' => $verificationCode];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function sendDriverPassword($phone, $code)
    {
        try {
            $message = 'Alwarda.sa : Your password is : ' . $code;
            $sms = new SmsMessage($phone, $message);
            $this->texter->send($sms);
            return ['success' => $code];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getShopDashBoardData($user)
    {
        $userChartData = $this->shopOrdersDashboardData($user);
        $data = [
            'month' => [
                'orders' => array_reverse($userChartData['month'], true),
            ],
            'week' => [
                'orders' => array_reverse($userChartData['week'], true),
            ],
            'year' => [
                'orders' => array_reverse($userChartData['year'], true),
            ]
        ];
        return $data;
    }

    public function shopOrdersDashboardData($user)
    {
        $lastMonthDays = [];
        $lastYearMonths = [];

        for ($i = 0; $i <= 30; $i++) {
            $now = new \DateTime();
            $lastMonthDays[$now->sub(date_interval_create_from_date_string($i . ' days'))
                ->format('d F Y')] = 0;
        }
        for ($i = 0; $i <= 11; $i++) {
            $now = new \DateTime();
            $lastYearMonths[$now->sub(date_interval_create_from_date_string($i . ' months'))
                ->format('F Y')] = 0;
        }
        $lastMonthDaysUsers = $this->orderRepositoty->countOrdersPerDateDashboard($lastMonthDays, $user);
        $lastYearMonthsUsers = $this->orderRepositoty->countOrdersPerMonthsDashboard($lastYearMonths, $user);

        foreach ($lastMonthDaysUsers as $el) {
            $lastMonthDays[$el['day']] = $el['count'];
        }
        $lastWeekDays = array_slice($lastMonthDays, 0, 7);

        foreach ($lastYearMonthsUsers as $el) {
            $lastYearMonths[$el['month']] = $el['count'];
        }

        return [
            'week' => $lastWeekDays,
            'month' => $lastMonthDays,
            'year' => $lastYearMonths
        ];
    }

    public function generateRandomDigitsCode(int $digits)
    {
        $chars = "0123456789";
        $code = "";
        for ($i = 0; $i < $digits; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public function sendMsgNotification(int $id, Message $message)
    {
        $topic = $id;

        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create('Alwarda', 'You have a new message'))
            ->withData([
                'id' => $message->getId(),
                'message' => $message->getMessage(),
                'date_time' => $message->getCreatedAt()->format('d-m-Y H:i'),
                'new' => $message->getNew()
            ]);
        $this->messaging->send($message);

    }
}

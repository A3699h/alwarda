<?php


namespace App\Controller\Front;


use App\Entity\Area;
use App\Entity\DeliveryAddress;
use App\Entity\DiscountVoucher;
use App\Entity\Order;
use App\Form\DeliveryAddressType;
use App\Form\FrontDeliveryAddressType;
use App\Form\FrontEditPasswordType;
use App\Repository\DeliveryAddressRepository;
use App\Repository\DiscountVoucherRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProfileController
 * @package App\Controller\Front
 * @Route("/profile")
 */
class ProfileController extends AbstractController
{

    /**
     * @Route("/",
     *     name="front_profile",
     *     options={"expose"=true}
     *     )
     */
    public function profile()
    {
        return $this->render('front/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/reset-password",
     *     name="front_reset_password"
     *     )
     */
    public function resetPassword(Request $request,
                                  UserPasswordEncoderInterface $encoder,
                                  EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $passwordForm = $this->createForm(FrontEditPasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $passwordForm->get('newPassword')->getData()));
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('front_profile');
        }
        return $this->render('front/resetPassword.html.twig', [
            'user' => $this->getUser(),
            'form' => $passwordForm->createView()
        ]);
    }

    /**
     * @Route("/manage-orders/{status}",
     *     name="front_manage_orders"
     *     )
     */
    public function manageOrders($status, OrderRepository $orderRepository)
    {
        $user = $this->getUser();
        if ($status == 'current') {
            $orders = $orderRepository->getCurrentOrders($user);
        } else {
            $orders = $orderRepository->getPreviousOrders($user);
        }
        return $this->render('front/manageOrders.html.twig', [
            'user' => $user,
            'orders' => $orders,
            'status' => $status
        ]);
    }

    /**
     * @param Request $request
     * @Route("/set-token/{id}",
     *     name="front_api_set_payment_auth_token",
     *     methods={"POST"},
     *     options={"expose"=true}
     * )
     */
    public function setPaymentToken(Order $order,
                                    Request $request)
    {
        $paymentAuthToken = $request->request->get('token');
        $http = new Client();
        try {
            $response = $http->request('POST', 'https://api.tap.company/v2/charges', [
                'headers' => [
                    'Authorization' => 'Bearer ' . "sk_test_VLHobO6jkNnfvArUXSqQFc1M"
//                    'Authorization' => 'Bearer ' . "sk_live_01NHnlwe8Bt4iO3XdYamPcz6"
                ],
                'json' => [
                    "amount" => $order->getTotalPrice(),
                    "currency" => "SAR",
                    "customer" => [
                        "first_name" => $order->getClient()->getFullName(),
                        "email" => $order->getClient()->getEmail(),
                        "phone" => [
                            "number" => $order->getClient()->getPhone()
                        ]
                    ],
                    "source" => [
                        "id" => $paymentAuthToken
                    ],
                    "redirect" => [
                        "url" => $this->generateUrl('front_api_check_payment', [
                            'id' => $order->getId()
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                return $this->json($response, Response::HTTP_OK);
            }
        } catch (GuzzleException $e) {
            return $this->json($e->getMessage());
        }
    }

    /**
     * @Route("/check-payment/{id}",
     *     name="front_api_check_payment",
     *     options={"expose"=true}
     * )
     */
    public function checkPayment(Order $order,
                                 Request $request,
                                 EntityManagerInterface $manager)
    {
        $chargeId = $request->query->get('tap_id');
        $http = new Client();
        try {
            $response = $http->request('GET', "https://api.tap.company/v2/charges/$chargeId", [
                'headers' => [
                    'Authorization' => 'Bearer ' . "sk_test_VLHobO6jkNnfvArUXSqQFc1M"
//                    'Authorization' => 'Bearer ' . "sk_live_01NHnlwe8Bt4iO3XdYamPcz6"
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $paymentStatus = json_decode($response->getBody()->getContents())->status;
                if ($paymentStatus == 'CAPTURED') {
                    // validate payment
                    $order->setPaymentStatus('paid');
                    $order->setPaymentTransaction($chargeId);
                    $manager->persist($order);
                    $manager->flush();

                    return $this->redirectToRoute('front_manage_orders', [
                        'status' => 'current',
                        'payment' => 'success'
                    ]);
                } else {
                    $order->setStatus('canceled');
                    $manager->flush();
                    return $this->redirectToRoute('front_manage_orders', [
                        'status' => 'current',
                        'payment' => 'failed'
                    ]);
                }
            }
        } catch (GuzzleException $e) {
            return $this->redirectToRoute('front_manage_orders', [
                'status' => 'current',
                'payment' => 'failed'
            ]);
        }
    }

    /**
     * @Route("/recipients-list",
     *     name="front_recipients-list"
     *     )
     */
    public function recipientsList(Request $request, EntityManagerInterface $manager)
    {
        $address = new DeliveryAddress();
        $form = $this->createForm(FrontDeliveryAddressType::class, $address);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $user->addDeliveryAdress($address);
            $manager->persist($address);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('front_recipients-list');
        }
        return $this->render('front/recipientsList.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'formInvalid' => $form->isSubmitted() && !$form->isValid()
        ]);
    }

    /**
     * @Route("/api/recipients/{area}",
     *     name="api_get_recipients_list",
     *     options={"expose"=true}
     *     )
     */
    public function apiGetRecipientsList(Area $area,
                                         DeliveryAddressRepository $deliveryAddressRepository)
    {
        $addresses = $deliveryAddressRepository->findActiveByUserandByArea($this->getUser(), $area);
        return $this->json($addresses, Response::HTTP_OK);
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/delivery-address/delete/{id}",
     *     name="front_delete_delivery_address"
     * )
     */
    public function deleteDeliveryAddress(DeliveryAddress $deliveryAddress, EntityManagerInterface $manager)
    {
        $deliveryAddress->setActive(false);
        $manager->flush();
        return $this->redirectToRoute('front_recipients-list');
    }

    /**
     * @Route("/token",
     *     name="front_api_get_token",
     *     options={"expose"=true},
     *     methods={"POST"}
     *     )
     */
    public function getToken(JWTTokenManagerInterface $tokenManager)
    {
        $user = $this->getUser();
        return $this->json($tokenManager->create($user), Response::HTTP_OK);
    }

    /**
     * @Route("/check-voucher/{code}",
     *     name="front_api_check_voucher",
     *     options={"expose"=true}
     *     )
     */
    public function checkVoucher($code,
                                 DiscountVoucherRepository $voucherRepository)
    {
        /** @var DiscountVoucher $voucher */
        $voucher = $voucherRepository->findOneByCode($code);
        if (!$voucher) {
            return $this->json('This voucher is not valid', Response::HTTP_NOT_FOUND);
        }
        if ($voucher->getUses() > $voucher->getMaxUse()) {
            return $this->json('Voucher reached its uses limit', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($voucher->getEndDate() < new \DateTime()) {
            return $this->json('This voucher is expired', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($voucher->getStartDate() > new \DateTime()) {
            return $this->json('This voucher is not active yet', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $clientOrders = $this->getUser()->getOrders();
        $voucherMaxPerClient = $voucher->getClientMaxUse();
        $clientUses = 0;
        foreach ($clientOrders as $clientOrder) {
            if ($clientOrder->getDiscountVoucher() == $voucher) {
                $clientUses++;
            }
        }
        if ($clientUses >= $voucherMaxPerClient) {
            return $this->json('This client reached the max uses for this voucher', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $this->json($voucher->getDiscountPercentage(), Response::HTTP_OK);
    }


}

<?php

namespace App\Controller\Dashboard;

use App\Entity\MessageFile;
use App\Entity\Policy;
use App\Entity\ProductColor;
use App\Entity\User;
use App\Form\PolicyType;
use App\Form\ProductColorType;
use App\Repository\DeliveryAddressRepository;
use App\Repository\MessageRepository;
use App\Repository\OrderRepository;
use App\Repository\PolicyRepository;
use App\Repository\ProductColorRepository;
use App\Repository\UserRepository;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;


class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(UtilsService $utilsService, OrderRepository $orderRepository)
    {
		 // get database connection
        $conn = $this->getDoctrine()->getConnection();
        // check if database is connected
        if ($conn->isConnected()) {
            // get database name
            $database = $conn->getDatabase();
            echo "Connected to the <strong>{$conn->getDatabase()}</strong> database successfully!";
        
        } else {
            // if not connected, try to connect
            $conn->connect();
            echo "Connected to the <strong>{$conn->getDatabase()}</strong> database successfully!";
        }
		$this->get('session')->set('activeMenu', 'dashboard');
        if ($this->getUser()->getRole() == User::USER_ROLES['shop']) {
            $chartData = $utilsService->getShopDashBoardData($this->getUser());
            $data = $utilsService->getDashboardDataShop($this->getUser());
            $data['nbProducts'] = count($this->getUser()->getAvailableProducts());
            return $this->render('dashboard/shop_index.html.twig', [
                'chartData' => json_encode($chartData),
                'orders' => $orderRepository->findByShop($this->getUser()),
                'data' => $data
            ]);
        }
        $data = $utilsService->getDashboardData();
        return $this->render('dashboard/index.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/chartData",
     *      name="chart_data",
     *     methods={"POST"},
     *     options={"expose"=true}
     *     )
     */
    public function chartData(UtilsService $utilsService)
    {
        $userChartData = $utilsService->getUsersChartData();
        $orderChartData = $utilsService->getOrdersChartData();
        return $this->json([
            'month' => [
                'users' => array_reverse($userChartData['month'], true),
                'orders' => $orderChartData['month']
            ],
            'week' => [
                'users' => array_reverse($userChartData['week'], true),
                'orders' => $orderChartData['week']
            ],
            'year' => [
                'users' => array_reverse($userChartData['year'], true),
                'orders' => $orderChartData['year']
            ]
        ], '200');
    }

    /**
     * @Route("/message-file/{id}", name="show_message_file")
     */
    public function showMessageFile(MessageFile $messageFile,
                                    EntityManagerInterface $entityManager,
                                    DownloadHandler $downloadHandler)
    {
        $messageFile->setViewedAt(new \DateTime());
        $entityManager->flush();
        return $downloadHandler->downloadObject($messageFile, 'fileFile');
    }

    /**
     * @Route("/message-file/{id}/download", name="download_message_file")
     */
    public function downloadMessageFile(MessageFile $messageFile,
                                        DownloadHandler $downloadHandler)
    {
        return $downloadHandler->downloadObject($messageFile, 'fileFile');
    }

    /**
     * @Route("/client/delivery-addresses/{id}",
     *     name="addressByClient",
     *     methods={"POST"},
     *     options={"expose"=true})
     */
    public function deliveryAddressesOfClient(Request $request,
                                              DeliveryAddressRepository $deliveryAddressRepository,
                                              User $user)
    {
        if ($request->isXmlHttpRequest()) {
            $deliveryAddress = $deliveryAddressRepository->findByClient($user->getId());
            $responseArray = [];
            foreach ($deliveryAddress as $address) {
                $responseArray[] = [
                    "id" => $address->getId(),
                    "address" => $address->getRecieverFullAddress()
                ];
            }
            return $this->json($responseArray, 200);
        }
        return $this->json('This is only accessible via AJAX !', Response::HTTP_FORBIDDEN);
    }

    /**
     * @Route("/privacy-policy", name="edit_privacy_policy")
     */
    public function editPrivacyPolicy(Request $request, PolicyRepository $policyRepository, EntityManagerInterface $manager)
    {
        $request->getSession()->set('activeMenu', 'privacy');

        $privacy = $policyRepository->find(1) ?? new Policy();
        $form = $this->createForm(PolicyType::class, $privacy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($privacy);
            $manager->flush();
            return $this->redirectToRoute('edit_privacy_policy');
        }

        return $this->render('params/privacy_policy.html.twig', [
            'privacy' => $privacy,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product-colors", name="edit_product_colors")
     */
    public function editProductColors(Request $request,
                                      EntityManagerInterface $manager,
                                      ProductColorRepository $productColorRepository)
    {
        $request->getSession()->set('activeMenu', 'colors');

        $color = new ProductColor();
        $form = $this->createForm(ProductColorType::class, $color);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($color);
            $manager->flush();
            return $this->redirectToRoute('edit_product_colors');
        }

        return $this->render('params/product_colors.html.twig', [
            'colors' => $productColorRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product-colors/{id}/delete", name="delete_product_color")
     */
    public function deleteProductColors(EntityManagerInterface $manager,
                                        ProductColor $color)
    {
        $manager->remove($color);
        $manager->flush();
        return $this->redirectToRoute('edit_product_colors');
    }

    /**
     * @Route("/checkMessages",
     *     methods={"POST"},
     *     name="check_new_messages",
     *     options = { "expose" = true }
     *     )
     */
    public function checkNewMessages(MessageRepository $messageRepository)
    {
        $newMessage = $messageRepository->checkNewMessages();
        return $this->json($newMessage > 0);
    }

}

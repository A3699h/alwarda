<?php

namespace App\Controller\Dashboard;

use App\Entity\Order;
use App\Entity\User;
use App\Form\DriverType;
use App\Form\ShopType;
use App\Form\UserType;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/{role}",
     *     name="user_index",
     *     methods={"GET"},
     *     requirements={"role" = "client|shop|admin|driver|all"})
     */
    public function index(UserRepository $userRepository, $role): Response
    {
        $this->get('session')->set('activeMenu', $role == 'all' ? 'users' : $role);
        if ($role === 'driver') {
            $template = 'user/drivers_index.html.twig';
        } else {
            $template = 'user/index.html.twig';
        }
        if ($role == 'all' && $this->getUser()->getRole() != User::USER_ROLES['superAdmin']) {
            $users = $userRepository->findNotAdmins();
        } elseif ($role == 'all' && $this->getUser()->getRole() == User::USER_ROLES['superAdmin']) {
            $users = $userRepository->findAll();
        } else {
            $users = $userRepository->findByRole(User::USER_ROLES[$role]);
        }
        return $this->render($template, [
            'users' => $users,
            'title' => $role
        ]);
    }

    /**
     * @Route("/new/{role}",
     *     name="user_new",
     *     methods={"GET","POST"},
     *      requirements={"role" = "client|admin|shop|driver"})
     */
    public function new(UserPasswordEncoderInterface $encoder, Request $request, string $role): Response
    {
        $user = new User();
        $user->setRole(User::USER_ROLES[$role]);
        if ($role == 'shop') {
            $form = $this->createForm(ShopType::class, $user);
        } elseif ($role == 'driver') {
            $form = $this->createForm(DriverType::class, $user);
        } else {
            $form = $this->createForm(UserType::class, $user);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $firstPassword = $request->request->get('user')['password']['first'];
            $secondPassword = $request->request->get('user')['password']['second'];
            if ($firstPassword !== '' && $secondPassword !== '') {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            }
            if ($role == 'driver' && !is_null($user->getAccessId())) {
                $user->setEmail($user->getAccessId());
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', ['role' => $role]);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title' => $role
        ]);
    }

    /**
     * @Route("/driver/new", name="driver_new", methods={"GET","POST"})
     */
    public function newDriver(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $user->setRole(User::USER_ROLES['driver']);
        $form = $this->createForm(DriverType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $firstPassword = $request->request->get('user')['password']['first'];
            $secondPassword = $request->request->get('user')['password']['second'];
            if ($firstPassword !== '' && $secondPassword !== '') {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            }
            $user->setEmail($user->getAccessId());
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index', ['role' => 'driver']);
        }

        return $this->render('user/new_driver.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user, OrderRepository $orderRepository, UtilsService $utilsService): Response
    {
        if ($user->getRole() == User::USER_ROLES['shop']) {
            $lastWeekDates = $utilsService->generateLastWeekDays();
            $lastWeekOrdersByShop = $orderRepository->findLastWeekOrdersByShop($user, $lastWeekDates);
            $totalAmount = 0;
            foreach ($lastWeekOrdersByShop as $order) {
                $totalAmount += $order->getTotalPrice();
            }
            return $this->render('user/shop_show.html.twig', [
                'shop' => $user,
                'orders' => $orderRepository->findBy(['shop' => $user]),
                'lastWeekOrders' => $lastWeekOrdersByShop,
                'dateFrom' => $lastWeekDates[0],
                'dateTo' => $lastWeekDates[6],
                'totalAmount' => $totalAmount
            ]);
        } elseif ($user->getRole() == User::USER_ROLES['client']) {
            return $this->render('user/client_show.html.twig', [
                'client' => $user,
            ]);
        } elseif ($user->getRole() == User::USER_ROLES['driver']) {
            $lastWeekDates = $utilsService->generateLastWeekDays();
            $lastWeekOrdersByDriver = $orderRepository->findLastWeekOrdersByDriver($user, $lastWeekDates);
            $totalAmountDelivered = 0;
            $totalAmountShipped = 0;
            $res = [];
            foreach ($lastWeekOrdersByDriver as $order) {
                $status = $order->getStatus();
                if ($status == 'shipped') {
                    $acceptedDate = $order->getAcceptedAt()->format('d-m-Y');
                    $res[$acceptedDate]['shipped'][] = $order;
                    $totalAmountShipped += $order->getTotalPrice();
                } else {
                    $deliveredDate = $order->getDeliveredAt()->format('d-m-Y');
                    $res[$deliveredDate]['delivered'][] = $order;
                    $totalAmountDelivered += $order->getTotalPrice();
                }
            }
            return $this->render('user/driver_show.html.twig', [
                'driver' => $user,
                'orders' => $res,
                'dateFrom' => $lastWeekDates[0],
                'dateTo' => $lastWeekDates[6],
                'totalAmountDelivered' => $totalAmountDelivered,
                'totalAmountShipped' => $totalAmountShipped
            ]);
        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        if ($user->getRole() == User::USER_ROLES['shop']) {
            $form = $this->createForm(ShopType::class, $user);
        } elseif ($user->getRole() == User::USER_ROLES['driver']) {
            $form = $this->createForm(DriverType::class, $user);
        } else {
            $form = $this->createForm(UserType::class, $user);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()->getPassword() !== '') {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            }
            if ($user->getRole() == User::USER_ROLES['driver']) {
                $user->setEmail($user->getAccessId());
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', ['role' => array_search($user->getRole(), User::USER_ROLES)]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title' => array_search($user->getRole(), User::USER_ROLES)
        ]);
    }

    /**
     * @Route("/{id}/delete", name="user_delete", methods={"GET"})
     */
    public function delete(User $user): Response
    {
        if ($user->getRole() == User::USER_ROLES['admin'] && $this->getUser()->getRole() != User::USER_ROLES['superAdmin']) {
            return new Response('You do not have permission to delete admins', 403);
        }
        if ($user->getRole() === User::USER_ROLES['superAdmin']) {
            $this->addFlash('error', 'Super Admin cannot be deleted');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'The User "' . $user->getEmail() . '" has been deleted');
        }
        return $this->redirectToRoute('user_index', ['role' => 'all']);
    }

    /**
     * @Route( "/{id}/toggle_active",
     *     methods={"POST"},
     *     name="toggle_user_active",
     *     options = { "expose" = true }
     *    )
     *
     */
    public function toggleActive(Request $request, User $user)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $user->setActive(!$user->getActive());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->json('User active attribute toggled', 200);
            } catch (\Exception $e) {
                return $this->json('Error :' . $e, 500);
            }
        }
        return new Response('This is only accessible via AJAX !');
    }

    /**
     * @Route("/balance/{type}/{id}", name="balance_control")
     */
    public function balance($type, User $user, Request $request, EntityManagerInterface $manager)
    {
        $amount = floatval($request->get('amount'));
        if ($user->getRole() == User::USER_ROLES['client']) {
            if ($type == 'add') {
                $user->setBalance($user->getBalance() + $amount);
                $manager->flush();
                $this->addFlash('success', 'Balance updated');
            } elseif ($type = 'deduct') {
                $actual = $user->getBalance();
                if ($actual >= $amount) {
                    $user->setBalance($actual - $amount);
                    $manager->flush();
                    $this->addFlash('success', 'Balance updated');
                } else {
                    $this->addFlash('error', 'The amount you entered is grater than the actual balance of the client');
                }
            }
        }
        return $this->redirectToRoute('user_show', [
            'id' => $user->getId()
        ]);
    }
}

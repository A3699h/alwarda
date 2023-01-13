<?php

namespace App\Controller\Dashboard;

use App\Entity\MessageFile;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\OrderNote;
use App\Entity\User;
use App\Form\OrderNoteType;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/{type}",
     *     name="order_index",
     *     methods={"GET"},
     *     requirements={"type" = "orders_all|orders_confirmed|orders_not_confirmed|orders_canceled"}
     *     )
     */
    public function index(OrderRepository $orderRepository, $type = null): Response
    {
        $subTitle = null;
        $status = null;
        $paymentStatus = null;

        switch ($type) {
            case 'orders_all':
                $subTitle = 'All';
                $this->get('session')->set('activeMenu', 'orders_all');
                break;
            case 'orders_confirmed':
                $subTitle = 'Confirmed';
                $status = 'new';
                $paymentStatus = 'paid';
                $this->get('session')->set('activeMenu', 'orders_confirmed');
                break;
            case 'orders_not_confirmed':
                $subTitle = 'Not confirmed';
                $status = 'new';
                $paymentStatus = 'not_paid';
                $this->get('session')->set('activeMenu', 'orders_not_confirmed');
                break;
            case 'orders_canceled':
                $subTitle = 'Canceled';
                $status = 'canceled';
                $this->get('session')->set('activeMenu', 'orders_canceled');
                break;
        };

        if ($this->getUser()->getRole() == User::USER_ROLES['shop']) {
            $this->get('session')->set('activeMenu', 'orders');
            $orders = $orderRepository->findNotAssignedOrders($this->getUser()->getArea());
            $orders = array_filter($orders, function ($el) {
                foreach ($el->getOrderDetails() as $detail) {
                    if (!$detail->getProduct()->getVisible()) {
                        return false;
                    }
                }
                return true;
            });
            $subTitle = 'Available';
        } else {
            if ($status && $paymentStatus) {
                $orders = $orderRepository->findBy([
                    'status' => $status,
                    'paymentStatus' => $paymentStatus
                ], ['id' => 'DESC']);
            } elseif ($status) {
                $orders = $orderRepository->findBy([
                    'status' => $status
                ], ['id' => 'DESC']);
            } else {
                $orders = $orderRepository->findBy([], ['id' => 'DESC']);
            }
        }
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'subTitle' => $subTitle ?? null,
        ]);

    }

    /**
     * @Route("/my-orders", name="shop_orders_index", methods={"GET"})
     */
    public function shopOrdersIndex(OrderRepository $orderRepository): Response
    {
        $this->get('session')->set('activeMenu', 'assignedOrders');

        return $this->render('order/index.html.twig', [
            'orders' => $orderRepository->findBy(['shop' => $this->getUser()], ['id' => 'DESC']),
            'subTitle' => 'Assigned'
        ]);
    }

    /**
     * @Route("/new", name="order_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        UtilsService $utilsService): Response
    {
        $order = new Order();
        $note = new OrderNote();
        $note->setUser($this->getUser());
        $order->addNote($note);
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setOrderOrigin('dashboard');
            $subTotalPrice = 0;
            foreach ($order->getOrderDetails() as $detail) {
//                if (is_null($detail->getProduct())) {
//                    $order->removeOrderDetail($detail);
//                    $this->addFlash('error', 'The order detail product cannot be empty');
//                    continue;
//                }
                $detail->setDiscountable($detail->getProduct()->getDiscountable());
                $detail->setPrice($detail->getProduct()->getPrice());
                $discount = $order->getDiscountVoucher() ? $order->getDiscountVoucher()->getDiscountPercentage() : 0;
                $detail->setDiscount($discount);
                $subTotalPrice += $detail->getSubTotalAfterDiscount();
            }
            $order->setSubtotalPrice($subTotalPrice);
            $order->setTotalPrice($order->getDeliveryAddress()->getRecieverArea()->getDeliveryPrice());
            if (is_null($order->getNotes()[0]->getNote()) || trim($order->getNotes()[0]->getNote()) == '') {
                $order->removeNote($order->getNotes()[0]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
            if ($order->getPaymentMethod() == 'wallet') {
                if ($order->getClient()->getBalance() < $order->getTotalPrice()) {
                    $this->addFlash('error', 'The total price of the order is grater than the personal balance of the client');
                } else {
                    $order->getClient()->setBalance($order->getClient()->getBalance() - $order->getTotalPrice());
                }
            }
            $now = new \DateTime();
            $order->setReference($order->getClient()->getId() . '-' . $order->getId() . '-' . $now->format('Y'));
            if (!is_null($order->getMessageFile()->getFile())) {
                $utilsService->generateQrCode($order->getMessageFile());
            }
            $entityManager->flush();
            if ($request->request->get('willView') == "1") {
                return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
            }
            $entityManager->flush();
            $this->addFlash('success', 'The new order has been created');
            return $this->redirectToRoute('order_index');
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="order_show", methods={"GET", "POST"})
     */
    public function show(Order $order, Request $request): Response
    {
        $note = new OrderNote();
        $form = $this->createForm(OrderNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setUser($this->getUser());
            $order->addNote($note);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($order);
            $entityManager->flush();
            $this->addFlash('success', 'The note has been created');
            return $this->redirectToRoute('order_show', [
                'id' => $order->getId()
            ]);
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="order_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Order $order): Response
    {
        $note = new OrderNote();
        $note->setUser($this->getUser());
        $order->addNote($note);
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        $subTotalPrice = 0;
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($order->getOrderDetails() as $detail) {
                $discount = $order->getDiscountVoucher() ? $order->getDiscountVoucher()->getDiscountPercentage() : 0;
                $detail->setDiscount($discount);
                $detail->setPrice($detail->getProduct()->getPrice());
                $subTotalPrice += $detail->getSubTotalAfterDiscount();
            }
            foreach ($order->getNotes() as $note) {
                if (is_null($note->getNote()) || trim($note->getNote()) == '') {
                    $order->removeNote($note);
                }
            }
            $order->setSubtotalPrice($subTotalPrice);
            $order->setTotalPrice($order->getDeliveryAddress()->getRecieverArea()->getDeliveryPrice());
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="order_delete", methods={"GET"})
     */
    public function delete(Order $order, KernelInterface $kernel, FileSystem $fileSystem): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $qrCode = $order->getMessageFile() ? $order->getMessageFile()->getQrCode() : null;
        if (!is_null($qrCode)) {
            $qrCodeFile = $kernel->getProjectDir() . '/public/qrCodes/' . $qrCode;
            if (file_exists($qrCodeFile)) {
                $fileSystem->remove($qrCodeFile);
            }
        }
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('success', 'The order REF: "' . $order->getReference() . '" has been deleted');
        return $this->redirectToRoute('order_index');
    }


    /**
     * @param Order $order
     * @param EntityManagerInterface $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/cancel/{id}",
     *          name="cancel_order")
     */
    public function cancelOrder(EntityManagerInterface $manager, Order $order = null)
    {
        if ($order) {
            if ($order->getPaymentStatus() == 'paid') {
                $client = $order->getClient();
                $client->setBalance($client->getBalance() + $order->getTotalPrice());
            }
            $order->setStatus('canceled');
            $manager->flush();
            $this->addFlash('success', 'The order REF: "' . $order->getReference() . '" has been canceled');
        }
        return $this->redirectToRoute('order_index');
    }
}

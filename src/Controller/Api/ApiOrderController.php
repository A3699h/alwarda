<?php


namespace App\Controller\Api;


use App\Entity\MessageFile;
use App\Entity\OrderImage;
use App\Form\ApiOrderType;
use App\Form\OrderType;
use App\Repository\DiscountVoucherRepository;
use App\Repository\OrderRepository;
use App\Service\ApiFormError;
use App\Service\UtilsService;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use App\Entity\Order;

class ApiOrderController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_orders",
     *     path="/client/orders",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns client orders",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getClientOrders(OrderRepository $orderRepository, Request $request)
    {
        $delivered = !is_null($request->get('delivered')) ? boolval($request->get('delivered')) : null;
        if (is_null($delivered)) {
            $orders = $orderRepository->findBy(['client' => $this->getUser()]);
        } else {
            $orders = $orderRepository->findOrdersByClientAndStatus($this->getUser(), $delivered);
        }
        return $this->json($orders, 200, [], ['client']);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_cancel_order",
     *     options={"expose"=true},
     *     path="/client/cancel/{id}",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Cancel an unpaid order"
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function cancelOrder(EntityManagerInterface $manager, Order $order = null)
    {
        if ($order && $order->getPaymentStatus() == 'not_paid') {
            $order->setStatus('canceled');
            $manager->flush();
            $this->addFlash('success', 'The order REF: "' . $order->getReference() . '" has been canceled');
            return $this->json('The order with reference : ' . $order->getReference() . ' is cancelled', Response::HTTP_OK);
        } elseif ($order && $order->getPaymentStatus() == 'paid' && $order->getPaymentTransaction()) {
            $order->setStatus('canceled');
            $order->setPaymentStatus('not_paid');
            $http = new Client();
            try {
                $response = $http->request('POST', 'https://api.tap.company/v2/refunds', [
                    'headers' => [
//                        'Authorization' => 'Bearer ' . "sk_live_01NHnlwe8Bt4iO3XdYamPcz6"
                        'Authorization' => 'Bearer ' . "sk_test_VLHobO6jkNnfvArUXSqQFc1M"
                    ],
                    'json' => [
                        'charge_id' => $order->getPaymentTransaction(),
                        'amount' => $order->getTotalPrice(),
                        "currency" => "SAR",
                        "reason" => "requested_by_customer"
                    ]
                ]);
                if ($response->getStatusCode() == 200) {
                    $manager->flush();
                    return $this->json('The order with reference : ' . $order->getReference() . ' is cancelled and refunded', Response::HTTP_OK);
                }
            } catch (GuzzleException $e) {
                return $this->json($e->getMessage());
            }

        }
        return $this->json('An error occured', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_cancel_order",
     *     path="/driver/cancel/{id}",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Cancel an order within 1 our from acceptance for driver"
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function cancelDriverOrder(EntityManagerInterface $manager, Order $order = null)
    {
        $now = new \DateTime();
        if (!$order) {
            return $this->json('The order does not exist', Response::HTTP_NOT_FOUND);
        }
        if ($order->getDriver() != $this->getUser()) {
            return $this->json('This order not belongs to this driver', Response::HTTP_BAD_REQUEST);
        }
        if ($order->getAcceptedAt() <= $now->add(new DateInterval('PT1H'))) {
            $order->setAcceptedAt(null);
            $order->setDriver(null);
            $manager->persist($order);;
            $manager->flush();
            return $this->json('The order with reference : ' . $order->getReference() . ' is cancelled', Response::HTTP_OK);
        }
        return $this->json('The order was accepted since more than one hour', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_order",
     *     options={"expose"=true},
     *     path="/client/order/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns client order details by ID",
     *     @Model(type=Order::class, groups={"client"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getClientOrder(Order $order = null)
    {
        if (empty($order)) {
            return $this->json('Order not found', Response::HTTP_NOT_FOUND);
        }
        try {
            if ($order->getClient() == $this->getUser()) {
                return $this->json($order, 200, [], ['client']);
            }
            return $this->json('Current user is not the order owner', Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @param OrderRepository $orderRepository
     * @Route("/last-order",
     *     name="front_api_last_order",
     *     options={"expose"=true}
     *     )
     * @Security(name="Bearer")
     */
    public function getLastOrder(OrderRepository $orderRepository)
    {
        $order = $orderRepository->findOneBy([
            'client' => $this->getUser()
        ], [
            'id' => 'DESC'
        ]);
        return $this->json($order, 200, [], ['client']);
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_order",
     *     path="/driver/order/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns driver order details by ID",
     *     @Model(type=Order::class, groups={"delivery", "deliveryShop"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getDriverOrder(Order $order = null)
    {
        if (empty($order)) {
            return $this->json('Order not found', Response::HTTP_NOT_FOUND);
        }
        try {
            return $this->json($order, 200, [], ["delivery", "deliveryShop"]);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_available_orders",
     *     path="/driver/available-orders",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns available orders for drivers",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"delivery", "deliveryShop"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getDriverAvailableOrders(OrderRepository $orderRepository)
    {
        $availableOrders = $orderRepository->findAvailableOrdersForDrivers($this->getUser());
        return $this->json($availableOrders, Response::HTTP_OK, [], ['delivery', "deliveryShop"]);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_assign_order_driver",
     *     path="/driver/order/{id}/assign",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Assign order given by its ID to current driver",
     *    @Model(type=Order::class, groups={"delivery", "deliveryShop"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function assignOrderToDriver(EntityManagerInterface $em, Order $order = null)
    {
        if (empty($order)) {
            return $this->json('Order not found', Response::HTTP_NOT_FOUND);
        }
        if (!is_null($order->getDriver())) {
            return $this->json('Order already assigned', Response::HTTP_NOT_FOUND);
        }
        try {
            $order->setAcceptedAt(new \DateTime());
            $this->getUser()->addDriverOrder($order);
            $em->flush();
            return $this->json($order, Response::HTTP_OK, [], ['delivery', "deliveryShop"]);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route(
     *     methods={"PUT"},
     *     name="api_driver_update_order_status",
     *     path="/driver/order/{id}/status",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Update order status for drivers ('shipped' or 'delivered')",
     *    @Model(type=Order::class, groups={"delivery", "deliveryShop"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function updateOrderStatusForDrivers(Request $request, EntityManagerInterface $em, Order $order = null)
    {
        if (empty($order)) {
            return $this->json('Order not found', Response::HTTP_NOT_FOUND);
        }
        if ($order->getDriver() != $this->getUser()) {
            return $this->json('Current user is not the order driver', Response::HTTP_FORBIDDEN);
        }
        $status = json_decode($request->getContent(), true)['status'] ?? null;
        if (!is_null($status) && in_array($status, [0, 1])) {
            if ($order->getStatus() == 'new') {
                if ($status == 0) {
                    try {
                        $order->setStatus('shipped');
                        $em->flush();
                        return $this->json($order, Response::HTTP_OK, [], ['delivery', "deliveryShop"]);
                    } catch (\Exception $e) {
                        return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
                    }
                }
                return $this->json('This order should be shipped before being delivered', Response::HTTP_BAD_REQUEST);
            } elseif ($order->getStatus() == 'shipped') {
                if ($status == 1) {
                    try {
                        $order->setDeliveredAt(new \DateTime());
                        $order->setStatus('delivered');
                        $em->flush();
                        return $this->json($order, Response::HTTP_OK, [], ['delivery', "deliveryShop"]);
                    } catch (\Exception $e) {
                        return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
                    }
                }
                return $this->json('This order is already shipped', Response::HTTP_BAD_REQUEST);
            } elseif ($order->getStatus() == 'delivered') {
                return $this->json('This order is already delivered', Response::HTTP_BAD_REQUEST);
            }
        }
        return $this->json('Status is not a valid value; should be \'shipped\' or \'delivered\' ', Response::HTTP_FORBIDDEN);
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_order_history",
     *     path="/driver/orders/history/{nbDays}",
     *     requirements={"nbDays"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns drivers orders history (nbDays is OPTIONAL and allows to limit data to last X days",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"delivery"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getOrdersHistoryForDriver(OrderRepository $orderRepository, $nbDays = 0)
    {
        $res = [];
        $orders = $orderRepository->getShippedDeliveredOrdersByDriver($this->getUser(), $nbDays);
        foreach ($orders as $order) {
            $status = $order->getStatus();
            if ($status == 'shipped') {
                $acceptedDate = $order->getAcceptedAt()->format('d-m-Y');
                $res[$acceptedDate]['shipped'][] = $order;
            } else {
                $deliveredDate = $order->getDeliveredAt()->format('d-m-Y');
                $res[$deliveredDate]['delivered'][] = $order;
            }
        }
        return $this->json($res, Response::HTTP_OK, [], ['delivery']);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_order_last_week",
     *     path="/driver/orders/history/last-week",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns drivers orders history for last week (from friday to friday)",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"delivery"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getOrdersHistoryLastWeekForDriver(OrderRepository $orderRepository,
                                                      UtilsService $utilsService)
    {
        $user = $this->getUser();
        $lastWeekDates = $utilsService->generateLastWeekDays();
        $lastWeekOrdersByDriver = $orderRepository->findLastWeekOrdersByDriver($user, $lastWeekDates);
        $totalAmountDelivered = 0;
        $totalAmountShipped = 0;
        $res = [];
        $orders = [];
        foreach ($lastWeekOrdersByDriver as $order) {
            $status = $order->getStatus();
            if ($status == 'shipped') {
                $acceptedDate = $order->getAcceptedAt()->format('d-m-Y');
                $orders[$acceptedDate][] = $order;
                $totalAmountShipped += $order->getTotalPrice();
            } else {
                $deliveredDate = $order->getDeliveredAt()->format('d-m-Y');
                $orders[$deliveredDate][] = $order;
                $totalAmountDelivered += $order->getTotalPrice();
            }
        }
        foreach ($orders as $date => $data) {
            $res['data'][] = [
                'date' => $date,
                'orders' => $data
            ];
        }

        $res['totalAmountDelivered'] = $totalAmountDelivered;
        $res['totalAmountShipped'] = $totalAmountShipped;

        $res['dateFrom'] = $lastWeekDates[0];
        $res['dateTo'] = $lastWeekDates[count($lastWeekDates) - 1];

        return $this->json($res, Response::HTTP_OK, [], ['delivery']);
    }


    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_client_add_order",
     *     options={"expose"=true},
     *     path="/client/order",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="Orders"}
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Add new order",
     *     @Model(type=Order::class, groups={"client"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function addOrder(Request $request,
                             ApiFormError $apiFormError,
                             EntityManagerInterface $em,
                             UtilsService $utilsService,
                             KernelInterface $kernel,
                             DiscountVoucherRepository $voucherRepository)
    {
        $order = new Order();
        $order->setStatus('new');
        $order->setClient($this->getUser());
        $order->setPaymentStatus('not_paid');
        $form = $this->createForm(ApiOrderType::class, $order, [
            'method' => 'POST',
            'csrf_protection' => false
        ]);
        $form->handleRequest($request);
        $form->submit($request->request->all());
        $voucherCode = $request->request->get('discountVoucher');
        if ($voucherCode) {
            $voucher = $voucherRepository->findOneByCode($voucherCode);
            if (!$voucher) {
                $form->addError(new FormError('Discount Voucher : This voucher is not valid'));
            }
            $order->setDiscountVoucher($voucher);
        }
        if ($order->getDiscountVoucher()) {
            if ($order->getDiscountVoucher()->getUses() > $order->getDiscountVoucher()->getMaxUse()) {
                $form->addError(new FormError('Discount Voucher : Voucher reached its uses limit'));
            }
            if ($order->getDiscountVoucher()->getEndDate() < new \DateTime()) {
                $form->addError(new FormError('Discount Voucher : This voucher is expired'));
            }
            if ($order->getDiscountVoucher()->getStartDate() > new \DateTime()) {
                $form->addError(new FormError('Discount Voucher : This voucher is not active yet'));
            }
            $clientOrders = $order->getClient()->getOrders();
            $voucherMaxPerClient = $order->getDiscountVoucher()->getClientMaxUse();
            $clientUses = 0;
            foreach ($clientOrders as $clientOrder) {
                if ($clientOrder->getDiscountVoucher() == $order->getDiscountVoucher()) {
                    $clientUses++;
                }
            }
            if ($clientUses >= $voucherMaxPerClient) {
                $form->addError(new FormError('Discount Voucher : This client reached the max uses for this voucher'));
            }
        }

        if (!$order->getClient()->getDeliveryAddresses()->contains($order->getDeliveryAddress())) {
            $form->addError(new FormError('Delivery address : This address is not attached to this user'));
        }
        if (!$form->isValid()) {
            return $apiFormError->jsonResponseFormError($form);
        }

        $subTotalPrice = 0;
        foreach ($order->getOrderDetails() as $detail) {
            $detail->setDiscountable($detail->getProduct()->getDiscountable());
            $detail->setPrice($detail->getProduct()->getPrice());
            $discount = $order->getDiscountVoucher() ? $order->getDiscountVoucher()->getDiscountPercentage() : 0;
            $detail->setDiscount($discount);
            $subTotalPrice += $detail->getSubTotalAfterDiscount();
        }
        $order->setSubtotalPrice($subTotalPrice);
        $order->setTotalPrice($order->getDeliveryAddress()->getRecieverArea()->getDeliveryPrice());
        if ($order->getDiscountVoucher()) {
            $order->getDiscountVoucher()->setUses($order->getDiscountVoucher()->getUses() + 1);
        }
        $file = $request->files->get('messageFile');
        if ($file) {
            $name = md5(uniqid()) . "." . $file->getClientOriginalExtension();
            $path = $kernel->getProjectDir() . '/public/client_files';
            $file->move($path, $name);

            $mf = new MessageFile();
            $mf->setFile($name);
            $order->setMessageFile($mf);
        }

        $em->persist($order);
        $em->flush();
        $now = new \DateTime();
        $order->setReference($order->getClient()->getId() . '-' . $order->getId() . '-' . $now->format('Y'));
        if ($file) {
            $utilsService->generateQrCode($order->getMessageFile());
        }
        $em->flush();

        return $this->json($order, 200, [], ['client']);
    }


    /**
     * @Route(
     *     methods={"PUT"},
     *     name="api_client_pay_order",
     *     path="/client/order/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Update the order payment status",
     *    @Model(type=Order::class, groups={"client"})
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function payOrder(Request $request, Order $order, EntityManagerInterface $em)
    {
        if (empty($order)) {
            return $this->json('Order not found', Response::HTTP_NOT_FOUND);
        }
        if ($order->getClient() != $this->getUser()) {
            return $this->json('Current user is not the order owner', Response::HTTP_FORBIDDEN);
        }

        $method = json_decode($request->getContent(), true)['paymentMethod'] ?? null;

        if (!in_array($method, Order::ORDER_PAYMENT_METHODS)) {
            return $this->json('This method is not allowed, only Visa, MasterCard and Wallet are allowedr', Response::HTTP_BAD_REQUEST);
        }
        if (!is_null($method)) {
            if ($method == 'wallet') {
                if ($order->getTotalPrice() > $order->getClient()->getBalance()) {
                    return $this->json('The client personal balance does not allow this payment', Response::HTTP_BAD_REQUEST);
                } else {
                    $order->getClient()->setBalance($order->getClient()->getBalance() - $order->getTotalPrice());
                    $em->persist($order->getClient());
                    $order->setPaymentStatus('paid');
                    $order->setPaymentMethod($method);
                    $order->setPaymentDate(new \DateTime());
                    $em->flush();
                    return $this->json('Order updated', Response::HTTP_OK);
                }
            }
        }
        return $this->json('Payment method is missing ', Response::HTTP_FORBIDDEN);
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_orders",
     *     path="/driver/orders",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns driver ordrrs",
     *    @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"delivery"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getDriverOrders()
    {
        $orders = $this->getUser()->getDriverOrders();
        return $this->json($orders, Response::HTTP_OK, [], ["delivery"]);
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_orders_to_deliver_today",
     *     path="/driver/orders/today",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns driver ordrrs with delivery date equals to today",
     *    @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Order::class, groups={"delivery"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function getDriverOrdersToDeliverToday(OrderRepository $orderRepository)
    {
        $orders = $orderRepository->findTodayDeliveryOrders($this->getUser());
        return $this->json($orders, Response::HTTP_OK, [], ["delivery"]);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_driver_add_order_image",
     *     path="/driver/order/{id}/add-image",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Add image to order"
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function addOrderImage(Request $request,
                                  Order $order,
                                  KernelInterface $kernel,
                                  EntityManagerInterface $manager)
    {
        try {
            $file = $request->files->get('image');
            if ($file) {
                $name = md5(uniqid()) . "." . $file->getClientOriginalExtension();
                $path = $kernel->getProjectDir() . '/public/images/orders';
                $file->move($path, $name);

                $image = new OrderImage();
                $image->setImage($name);
                $order->addImage($image);

                $manager->persist($image);
                $manager->flush();
                return $this->json($image, Response::HTTP_OK, [], ["delivery"]);
            }
            return $this->json('Image not found', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json('Error : ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route(
     *     methods={"DELETE"},
     *     name="api_driver_delete_order_image",
     *     path="/driver/order/image/{id}",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Delete image order"
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function deleteOrderImage(OrderImage $image,
                                     EntityManagerInterface $manager)
    {
        try {
            $order = $image->getParentOrder();
            $order->removeImage($image);
            $manager->remove($image);
            $manager->persist($order);
            $manager->flush();

            return $this->json('Image deleted', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json('Error : ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_driver_get_orders_stats",
     *     path="/driver/orders-stats",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="orders"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Get orders stats for driver"
     * )
     * @SWG\Tag(name="Order")
     * @Security(name="Bearer")
     *
     */
    public function driverOrdersStats(OrderRepository $orderRepository)
    {
        $all = $this->getUser()->getDeliverOrdersCount();
        $accepted = intval($orderRepository->countAcceptedOrders($this->getUser()));
        $shipped = intval($orderRepository->countShippedOrders($this->getUser()));
        $delivered = intval($orderRepository->countDeliveredOrders($this->getUser()));

        return $this->json(compact('all', 'accepted', 'shipped', 'delivered'), Response::HTTP_OK, []);
    }
}

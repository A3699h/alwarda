<?php


namespace App\Controller\Api;


use App\Entity\Order;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;

class ApiPaymentController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_request_payment",
     *     path="/client/payment/request/{id}",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="pay"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Order request link ",
     *     @Model(type=Order::class, groups={"client"})
     * )
     * @SWG\Tag(name="Payment")
     * @Security(name="Bearer")
     *
     */
    public function requestPayment(Order $order,
                                   EntityManagerInterface $manager,
                                   PaymentService $paymentService)
    {
        list($res, $status) = $paymentService->addInvoice($order);
        $order->setPaymentTransaction($res['transactionNo']);
        $manager->flush();
        return $this->json($res, $status);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_validate_payment",
     *     path="/client/payment/validate/{orderId}/{chargeId}",
     *     defaults={"_api_resource_class"=Order::class, "_api_collection_operation_name"="pay"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Order request link ",
     *     @Model(type=Order::class, groups={"client"})
     * )
     * @SWG\Tag(name="Payment")
     * @Security(name="Bearer")
     * @ParamConverter("order", options={"id" = "orderId"})
     *
     */
    public function validatePayment(Order $order, $chargeId, EntityManagerInterface $manager)
    {
//        list($res, $status) = $paymentService->getInvoice($order, $chargeId);
        $order->setPaymentStatus('paid');
        $order->setPaymentTransaction($chargeId);
        $manager->persist($order);
        $manager->flush();

        return $this->json($order,200,[],['client']);
    }
}

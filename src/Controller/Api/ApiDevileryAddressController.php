<?php


namespace App\Controller\Api;


use App\Form\DeliveryAddressType;
use App\Repository\AreaRepository;
use App\Service\ApiFormError;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use App\Entity\DeliveryAddress;

class ApiDevileryAddressController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_delivery_addresses",
     *     path="/client/delivery-addresses",
     *     defaults={"_api_resource_class"=DeliveryAddress::class, "_api_collection_operation_name"="deliveryAddresses"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the current client delivery addresses",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=DeliveryAddress::class, groups={"client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Delivery Address")
     * @Security(name="Bearer")
     *
     */
    public function getClientDeliveryAddress()
    {
        return $this->json($this->getUser()->getDeliveryAddresses(), 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_client_add_delivery_address",
     *     path="/client/delivery-address",
     *     defaults={"_api_resource_class"=DeliveryAddress::class, "_api_collection_operation_name"="deliveryAddresses"}
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Adds delivery address to the current client",
     *     @Model(type=DeliveryAddress::class, groups={"client"})
     * )
     * @SWG\Tag(name="Delivery Address")
     * @Security(name="Bearer")
     *
     */
    public function addClientDeliveryAddress(Request $request,
                                             ApiFormError $apiFormError,
                                             EntityManagerInterface $em)
    {

        try {
            $deliveryAddress = new DeliveryAddress();
            $form = $this->createForm(DeliveryAddressType::class, $deliveryAddress, [
                'method' => 'POST',
                'csrf_protection' => false
            ]);
            $form->handleRequest($request);
            $form->submit($request->request->all());
            if (!$form->isValid()) {
                return $apiFormError->jsonResponseFormError($form);
            }
            $this->getUser()->addDeliveryAdress($deliveryAddress);
            $em->persist($deliveryAddress);
            $em->flush();
            return $this->json($deliveryAddress, Response::HTTP_CREATED, [], ['client']);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route(
     *     methods={"PUT"},
     *     name="api_client_edit_delivery_address",
     *     path="/client/delivery-address/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=DeliveryAddress::class, "_api_collection_operation_name"="deliveryAddresses"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Edits delivery address",
     *     @Model(type=DeliveryAddress::class, groups={"client"})
     * )
     * @SWG\Tag(name="Delivery Address")
     * @Security(name="Bearer")
     *
     */
    public function editClientDeliveryAddress(DeliveryAddress $deliveryAddress,
                                              Request $request,
                                              ApiFormError $apiFormError,
                                              EntityManagerInterface $em)
    {

        try {
            $form = $this->createForm(DeliveryAddressType::class, $deliveryAddress, [
                'method' => 'PUT',
                'csrf_protection' => false
            ]);
            $submittedData = json_decode($request->getContent(), true);
            $submittedData = array_filter($submittedData, function ($el) {
                return $el != '';
            });
            $form->submit($submittedData, false);
            if (!$form->isValid()) {
                return $apiFormError->jsonResponseFormError($form);
            }
            $em->persist($deliveryAddress);
            $em->flush();
            return $this->json($deliveryAddress, 200, [], ['client']);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

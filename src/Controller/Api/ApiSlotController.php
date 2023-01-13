<?php


namespace App\Controller\Api;


use App\Repository\SlotRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Slot;
use Swagger\Annotations as SWG;

class ApiSlotController extends AbstractApiController
{
    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_slots",
     *     path="/client/slots",
     *     options={"expose"=true},
     *     defaults={"_api_resource_class"=Slot::class, "_api_collection_operation_name"="slots"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns available slots",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Slot::class, groups={"slot", "client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Delivery Slot")
     * @Security(name="Bearer")
     *
     */
    public function getSlots(SlotRepository $slotRepository)
    {
        return $this->json($slotRepository->findAvailableSlots(), 200, [], ["slot", "client"]);
    }
}

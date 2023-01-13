<?php


namespace App\Controller\Api;


use App\Repository\AreaRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Area;
use Swagger\Annotations as SWG;


class ApiAreaController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_areas",
     *     path="/areas",
     *     options={"expose"=true},
     *     defaults={"_api_resource_class"=Area::class, "_api_collection_operation_name"="areas"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns all areas",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Area::class, groups={"area"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Area")
     *
     */
    public function getAreas(AreaRepository $areaRepository)
    {
        return $this->json($areaRepository->findBy(['active' => true]), 200, [], ["area"]);
    }
}

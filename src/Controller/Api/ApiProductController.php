<?php


namespace App\Controller\Api;


use App\Repository\ProductRepository;
use Doctrine\DBAL\Driver\IBMDB2\DB2Driver;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductColorRepository;

class ApiProductController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_products",
     *     path="/products",
     *     defaults={"_api_resource_class"=Product::class, "_api_collection_operation_name"="products"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns active and available products",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Product::class, groups={"client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Product")
     * @Security(name="Bearer")
     *
     */
    public function getProducts(ProductRepository $productRepository,
                                Request $request)
    {
        $filters = [
            'color' => $request->get('color'),
            'type' => $request->get('type'),
            'minPrice' => $request->get('min_price'),
            'maxPrice' => $request->get('max_price'),
            'search' => $request->get('search'),
            'categoryId' => $request->get('category_id'),
            'package' => $request->get('package')
        ];
        return $this->json($productRepository->getStockedProducts(null, $filters), 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_most_sold_products",
     *     path="/products/most-sold/{nbResults}",
     *     requirements={"nbResults"="\d+"},
     *     defaults={"_api_resource_class"=Product::class, "_api_collection_operation_name"="products"},
     *     options={"expose"=true}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns MOST SOLD active and available products. (nbResults is optional and has 10 as default value)",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Product::class, groups={"client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Product")
     *
     */
    public function getMostSoldProducts(ProductRepository $productRepository, int $nbResults = 10)
    {
        $data = $productRepository->getMostSoldProducts($nbResults);
        $res = [];
        foreach ($data as $el) {
            $el[0]->setCountFromServer($el['count']);
            $res[] = $el[0];
        }
        return $this->json($res, 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_get_product",
     *     path="/product/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"_api_resource_class"=Product::class, "_api_collection_operation_name"="products"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Fetch and return product by it's ID",
     *     @Model(type=Product::class, groups={"client"})
     * )
     * @SWG\Tag(name="Product")
     * @Security(name="Bearer")
     *
     */
    public function getProduct(EntityManagerInterface $em, Product $product)
    {
        $product->setViews($product->getViews() + 1);
        $em->flush();
        return $this->json($product, 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_client_products_by_ids",
     *     path="/products/ids",
     *     defaults={"_api_resource_class"=Product::class, "_api_collection_operation_name"="products"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns products by their IDs",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Product::class, groups={"client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Product")
     * @Security(name="Bearer")
     *
     */
    public function getProductsByIds(Request $request, ProductRepository $productRepository)
    {
        $submittedData = json_decode($request->getContent());
        $data = [];
        foreach ($submittedData as $el) {
            $product = $productRepository->find($el->id);
            if (is_null($product)) {
                $data[] = [
                    'id' => $el->id
                ];
            } else {
                $product->setCountFromServer($el->count);
                $data[] = $product;
            }
        }
        return $this->json($data, Response::HTTP_OK, [], ["client"]);
    }
}

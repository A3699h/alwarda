<?php


namespace App\Controller\Api;


use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Product;
use Swagger\Annotations as SWG;

class ApiCategoryController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_categories",
     *     path="/categories",
     *     options={"expose"=true},
     *     defaults={"_api_resource_class"=Category::class, "_api_collection_operation_name"="categories"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns categories and their products",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Category::class, groups={"category", "client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Category")
     *
     */
    public function getCategories(CategoryRepository $categoryRepository)
    {
        return $this->json($categoryRepository->findAll(), 200, [], ["category", "client"]);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_category_products",
     *     path="/category/{id}/products",
     *     defaults={"_api_resource_class"=Category::class, "_api_collection_operation_name"="categories"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns all products of a category by it's ID",
     *     @SWG\Schema(
     *         type="array",
     *          @SWG\Items(
     *              ref=@Model(type=Category::class, groups={"category", "client"})
     *          )
     *     )
     * )
     * @SWG\Tag(name="Category")
     *
     */
    public function getCategoryProducts(ProductRepository $productRepository, Category $category)
    {
        return $this->json($productRepository->getStockedProducts($category), 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_front_category_products_limited",
     *     path="/category/{id}/products/{limit}",
     *     options={"expose"=true}
     * )
     *
     */
    public function getCategoryProductsLimited(ProductRepository $productRepository, Category $category, $limit)
    {
        return $this->json($productRepository->getStockedProductsLimited($category, $limit), 200, [], ["client"]);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_front_catalog_filtred",
     *     path="/catalog",
     *     options={"expose"=true}
     * )
     *
     */
    public function getCategoryProductsFiltredPaginated(ProductRepository $productRepository,
                                                        Request $request)
    {
        $pagination = $productRepository->getStockedProductsFiltredPaginated($request->request->get('filters'));
        return $this->json([
            'products'=> $pagination->getItems(),
            'page'=> $pagination->getCurrentPageNumber(),
            'pages'=>ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())
        ], 200, [], ["client"]
        );
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_front_categories_having_products",
     *     path="/stocked-categories",
     *     options={"expose"=true}
     * )
     *
     */
    public function getCategoruesHavingProducts(CategoryRepository $categoryRepository)
    {
        return $this->json($categoryRepository->findCategoriesHavingProducts(), 200, [], ["category"]
        );
    }
}

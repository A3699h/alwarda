<?php


namespace App\Controller\Front;


use App\Entity\Blog;
use App\Repository\AreaRepository;
use App\Repository\BlogRepository;
use App\Repository\CategoryRepository;
use App\Repository\FAQRepository;
use App\Repository\ParamsRepository;
use App\Repository\ProductColorRepository;
use App\Repository\ProductImageRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    /**
     * @Route("/",
     *     name="front_index",
     *     options={"expose"=true}
     *     )
     */
    public function index(AreaRepository $areaRepository,
                          CategoryRepository $categoryRepository,
                          FAQRepository $FAQRepository)
    {
        return $this->render('front/index.html.twig', [
//            'areas' => $areaRepository->findBy(['active' => true]),
            'categories' => $categoryRepository->findCategoriesHavingProducts(),
            'faqs' => $FAQRepository->findAll()
        ]);
    }

    /**
     * @Route("/catalog",
     *     name="front_catalog",
     *     options={"expose"=true}
     *     )
     */
    public function catalog(CategoryRepository $categoryRepository,
                            ProductRepository $productRepository,
                            ProductColorRepository $productColorRepository,
                            ProductImageRepository $productImageRepository)
    {

        $products = $productRepository->findAll();
        $images_array = [];
        $images_html = [];
        foreach ($products as $product) {
            $image_id = $product->getImages()[0]->getId();
            $image = $productImageRepository->find($image_id);
            $images_array[$product->getId()] = $image->getImage();
            $image_html = '<img src="http://iwoztla.cluster031.hosting.ovh.net/images/products/'.$image->getImage().'" alt="product image">';
            $images_html[$product->getId()] = $image_html;
        }
        // print_r($images_array);
        if (isset($_GET['category'])) {
            return $this->render('front/catalog.html.twig', [
                'categories' => $categoryRepository->findCategoriesHavingProducts(),
                'colors' => $productColorRepository->findHavingProducts(),
                'maxPrice' => $productRepository->getMaxPrice(),
                'products' => $categoryRepository->find($_GET['category'])->getProducts(),
                'images' => $images_array,
                'images_html' => $images_html
            ]);
        } else {
            return $this->render('front/catalog.html.twig', [
                'categories' => $categoryRepository->findCategoriesHavingProducts(),
                'colors' => $productColorRepository->findHavingProducts(),
                'maxPrice' => $productRepository->getMaxPrice(),
                'products' => $productRepository->findAll(),
                'images' => $images_array,
                'images_html' => $images_html
            ]);
        }
    }

    /**
     * @Route("/blog",
     *     name="front_blog_list",
     *     options={"expose"=true}
     *     )
     */
    public function blogList(BlogRepository $blogRepository)
    {
        $pagination = $blogRepository->getBlogsPaginated(1);
        return $this->render('front/blogList.html.twig', [
            'articles' => $pagination->getItems(),
            'pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())
        ]);
    }

    /**
     * @Route("/blog/{slug}",
     *     name="front_blog_single",
     *     options={"expose"=true}
     *     )
     */
    public function blogSingle(Blog $blog)
    {
        return $this->render('front/blogSingle.html.twig', [
            'article' => $blog
        ]);
    }


    /**
     * @Route("/blogs/{page}",
     *     name="api_get_blogs",
     *     options={"expose"=true})
     */
    public function apiGetArticles($page, BlogRepository $blogRepository)
    {
        $pagination = $blogRepository->getBlogsPaginated($page);
        return $this->json([
            'articles' => $pagination->getItems(),
            'pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/get-params",
     *     name="front_get_params",
     *     options={"expose"=true}
     *     )
     */
    public function getParams(ParamsRepository $paramsRepository)
    {
        return $this->json($paramsRepository->findAll(), Response::HTTP_OK);
    }

}

<?php

namespace App\Controller\Dashboard;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\StockInventory;
use App\Entity\User;
use App\Form\ProductType;
use App\Form\StockInventoryType;
use App\Repository\OrderDetailRepository;
use App\Repository\ProductImageRepository;
use App\Repository\ProductRepository;
use App\Repository\StockInventoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        $this->get('session')->set('activeMenu', 'products');
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            dd($product->getImages());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="product_delete", methods={"GET"})
     */
    public function delete(Request $request, Product $product): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'The product "' . $product->getName() . '" has been deleted');
        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/image/{imgName}/{imgExt}/delete",
     *     name="delete_product_image",
     *     methods={"POST"},
     *     options = { "expose" = true }
     *     )
     */
    public function deleteImage(ProductImageRepository $productImageRepository, Request $request, $imgName, $imgExt)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                $imageName = $imgName . '.' . $imgExt;
                $productImage = $productImageRepository->findOneByImage($imageName);
                $entityManager->remove($productImage);
                $entityManager->flush();
                return $this->json('Product image deleted', 200);
            } catch (\Exception $e) {
                return $this->json('Error :' . $e, 500);
            }

        }
        return new Response('This is only accessible via AJAX !');
    }

    /**
     * @Route( "/{id}/toggle_enabled",
     *     methods={"POST"},
     *     name="toggle_product_enabled",
     *     options = { "expose" = true }
     *    )
     *
     */
    public function toggleEnabled(Request $request, Product $product)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $product->setEnabled(!$product->getEnabled());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->json('Product enabled attribute toggled', 200);
            } catch (\Exception $e) {
                return $this->json('Error :' . $e, 500);
            }
        }
        return new Response('This is only accessible via AJAX !');
    }

}

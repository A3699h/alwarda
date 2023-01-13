<?php


namespace App\Controller\Dashboard;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class InventoryController extends AbstractController
{
    /**
     * Products to show for shops
     * @Route("/products/available",
     *     name="products_for_shops")
     */
    public function productsShow(ProductRepository $productRepository)
    {
        $this->get('session')->set('activeMenu', 'availableProducts');
        $availableProductsIds = [0];
        foreach ($this->getUser()->getAvailableProducts() as $product) {
            $availableProductsIds[] = $product->getId();
        }
        return $this->render('inventory/products.html.twig', [
            'products' => $productRepository->findProductsNotProvidedByShop($availableProductsIds),
            'title' => 'available'
        ]);
    }

    /**
     * Products to show for shops
     * @Route("/products/provided",
     *     name="shop_provided_products")
     */
    public function providedProductsShow()
    {
        $this->get('session')->set('activeMenu', 'providedProducts');
        return $this->render('inventory/products.html.twig', [
            'products' => $this->getUser()->getAvailableProducts(),
            'title' => 'provided'
        ]);
    }

    /**
     * @Route("/products/{id}/add",
     *     name="add_product_to_shop",
     *     methods={"POST"},
     *     options={"expose" = true})
     */
    public function addProductToShop(Request $request, Product $product)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                $product->addUser($this->getUser());
                $entityManager->persist($this->getUser());
                $entityManager->flush();
                return $this->json('Product added to provided products', 200);
            } catch (\Exception $e) {
                return $this->json('Error :' . $e, 500);
            }
        }
        return new Response('This is only accessible via AJAX !');
    }

    /**
     * @Route("/products/{id}/assign",
     *     name="assign_product_to_shop",
     *     methods={"GET"})
     */
    public function assignProductToShop(Request $request, Product $product)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product->addUser($this->getUser());
        $entityManager->persist($this->getUser());
        $entityManager->flush();
        $this->addFlash('success', 'The  order has been assigned');

        return $this->redirectToRoute('products_for_shops');
    }

    /**
     * @Route("/products/{id}/unassign",
     *     name="unassign_product_from_shop",
     *     methods={"GET"})
     */
    public function unassignProductFromShop(Request $request, Product $product)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product->removeUser($this->getUser());
        $entityManager->persist($this->getUser());
        $entityManager->flush();
        $this->addFlash('success', 'The  order has been un-assigned');

        return $this->redirectToRoute('products_for_shops');
    }

    /**
     * @Route("/products/{id}/remove",
     *     name="remove_product_from_shop",
     *     methods={"POST"},
     *     options={"expose" = true})
     */
    public function removeProductFromShop(Request $request, Product $product)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                $product->removeUser($this->getUser());
                $entityManager->persist($this->getUser());
                $entityManager->flush();
                return $this->json('Product removed from provided products', 200);
            } catch (\Exception $e) {
                return $this->json('Error :' . $e, 500);
            }
        }
        return new Response('This is only accessible via AJAX !');
    }

    /**
     * @param Product $product
     * @return Response
     * @Route("/product-show/{id}",
     *     name="shop_product_show")
     */
    public function productShow(Product $product)
    {
        return $this->render('product/shop_show.html.twig', [
            'product' => $product,
            'productColors' => Product::PRODUCT_COLORS
        ]);
    }

    /**
     * @param Order $order
     * @param EntityManagerInterface $em
     * @Route("/assign-order/{id}",
     *     name="assign_order")
     */
    public function assignOrder(Order $order, EntityManagerInterface $em)
    {
        $order->setShop($this->getUser());
        $em->flush();
        return $this->redirectToRoute('order_index');
    }

}

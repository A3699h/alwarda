<?php


namespace App\Controller\Api;


use App\Entity\DiscountVoucher;
use App\Entity\Product;
use App\Repository\DiscountVoucherRepository;
use App\Repository\FAQRepository;
use App\Repository\PolicyRepository;
use App\Repository\ProductColorRepository;
use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use App\Entity\Policy;
use App\Entity\FAQ;

class ApiMainController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_privacy_policy",
     *     path="/privacy-policy",
     *     defaults={"_api_resource_class"=Policy::class, "_api_collection_operation_name"="policies"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns privacy policy in english and arabic ",
     *    @Model(type=Policy::class)
     * )
     * @SWG\Tag(name="Various")
     *
     */
    public function getPrivacyPolicy(PolicyRepository $policyRepository)
    {
        return $this->json($policyRepository->find(1), 200);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_products_settings",
     *     path="/products-settings",
     *     defaults={"_api_resource_class"=Policy::class, "_api_collection_operation_name"="policies"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns products settings (colors, types, min_price and max_price) ",
     * )
     * @SWG\Tag(name="Various")
     *
     */
    public function getProductsSettings(ProductColorRepository $productColorRepository,
                                        ProductRepository $productRepository)
    {
        $minMaxPrice = $productRepository->getMinMaxPrice();
        $response = [
            'colors' => $productColorRepository->findAll(),
            'types' => Product::PRODUCT_TYPES,
            'min_price' => $minMaxPrice['min'],
            'max_price' => $minMaxPrice['max']
        ];
        return $this->json($response, Response::HTTP_OK, [], ["client"]);
    }


    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_faq",
     *     path="/faq",
     *     defaults={"_api_resource_class"=FAQ::class, "_api_collection_operation_name"="faq"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns FAQ questions and responses ",
     * )
     * @SWG\Tag(name="Various")
     *
     */
    public function getFaq(FAQRepository $FAQRepository)
    {
        return $this->json($FAQRepository->findAll(), Response::HTTP_OK);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_check_voucher",
     *     path="/check-voucher/{code}",
     *     defaults={"_api_resource_class"=DiscountVoucher::class, "_api_collection_operation_name"="DiscountVoucher"}
     * )
     * @ParamConverter("voucher", options={"mapping": {"code": "code"}})
     * @SWG\Response(
     *     response=200,
     *     description="Checks the validity of the discount voucher ",
     * )
     * @SWG\Tag(name="Discount Vouchers")
     *
     */
    public function checkVoucher(DiscountVoucher $voucher = null)
    {
        if (!$voucher) {
            $error = 'There is no Discount Voucher with this code';
            return $this->json(['error'=>$error], Response::HTTP_NOT_FOUND);
        }
        if ($voucher->getEndDate() < new \DateTime()) {
            $error = 'This voucher is expired';
            return $this->json(['error'=>$error], Response::HTTP_BAD_REQUEST);
        }
        if ($voucher->getStartDate() > new \DateTime()) {
            $error = 'This voucher is not active yet';
            return $this->json(['error'=>$error], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($voucher, Response::HTTP_OK,[],["client"]);
    }
}

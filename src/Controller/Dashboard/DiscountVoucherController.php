<?php

namespace App\Controller\Dashboard;

use App\Entity\DiscountVoucher;
use App\Form\DiscountVoucherType;
use App\Repository\DiscountVoucherRepository;
use App\Service\UtilsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/discount-voucher")
 */
class DiscountVoucherController extends AbstractController
{
    /**
     * @Route("/", name="discount_voucher_index", methods={"GET", "POST"})
     */
    public function index(DiscountVoucherRepository $discountVoucherRepository, Request $request, UtilsService $utilsService): Response
    {
        $this->get('session')->set('activeMenu', 'discountVouchers');
        $voucher = new DiscountVoucher();
        $voucher->setCode($utilsService->generateRandomVoucherCode(7));
        $form = $this->createForm(DiscountVoucherType::class, $voucher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($voucher);
            $entityManager->flush();
            $this->addFlash('success', 'A new discount voucher has been added');
            return $this->redirectToRoute('discount_voucher_index');
        }

        return $this->render('discount_voucher/index.html.twig', [
            'discountVouchers' => $discountVoucherRepository->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'formInvalid' => $form->isSubmitted() && !$form->isValid()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="discount_voucher_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DiscountVoucher $discountVoucher): Response
    {
        $form = $this->createForm(DiscountVoucherType::class, $discountVoucher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The voucher "'.$discountVoucher->getCode().'" has been updated');
            return $this->redirectToRoute('discount_voucher_index');
        }

        return $this->render('discount_voucher/edit.html.twig', [
            'discount_voucher' => $discountVoucher,
            'form' => $form->createView(),
        ]);
    }
}

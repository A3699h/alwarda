<?php

namespace App\Controller\Dashboard;

use App\Entity\FAQ;
use App\Form\FAQType;
use App\Repository\FAQRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faq")
 */
class FAQController extends AbstractController
{
    /**
     * @Route("/", name="faq_index", methods={"GET", "POST"})
     */
    public function index(FAQRepository $faqRepository, Request $request): Response
    {
        $this->get('session')->set('activeMenu', 'faq');

        $faq = new FAQ();
        $form = $this->createForm(FAQType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($faq);
            $entityManager->flush();
            $this->addFlash('success', 'A new faq section  has been added');
            return $this->redirectToRoute('faq_index');
        }

        return $this->render('faq/index.html.twig', [
            'faqs' => $faqRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="faq_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, FAQ $faq): Response
    {
        $form = $this->createForm(FAQType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('faq_index');
        }

        return $this->render('faq/edit.html.twig', [
            'faq' => $faq,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="faq_delete", methods={"GET"})
     */
    public function delete(Request $request, FAQ $faq, EntityManagerInterface $manager): Response
    {
        $manager->remove($faq);
        $manager->flush();
        $this->addFlash('success', 'The section "' . $faq->getQuestion() . '" has been deleted');
        return $this->redirectToRoute('faq_index');
    }
}

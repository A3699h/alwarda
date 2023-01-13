<?php


namespace App\Controller\Dashboard;


use App\Entity\Slot;
use App\Form\SlotEditType;
use App\Form\SlotType;
use App\Repository\SlotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SlotController
 * @package App\Controller
 * @Route("/slot")
 */
class SlotController extends AbstractController
{

    /**
     * @Route("/",  name="slot_index",   methods={"POST", "GET"} )
     */
    public function index(SlotRepository $slotRepository, Request $request)
    {
        $this->get('session')->set('activeMenu', 'slots');

        $slot = new Slot();
        $form = $this->createForm(SlotType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($slot);
            $entityManager->flush();
            $this->addFlash('success', 'A new slot has been added');
            return $this->redirectToRoute('slot_index');
        }

        return $this->render('slot/index.html.twig', [
            'slots' => $slotRepository->findBy([], ['id' => 'DESC']),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name="slot_delete", methods={"GET"})
     */
    public function delete(Slot $slot): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($slot);
        $entityManager->flush();
        $this->addFlash('success', 'The area "' . $slot->getName() . '" has been deleted');
        return $this->redirectToRoute('slot_index');
    }

    /**
     * @Route("/{id}/edit", name="slot_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Slot $slot): Response
    {
        $form = $this->createForm(SlotEditType::class, $slot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('slot_index');
        }

        return $this->render('slot/edit.html.twig', [
            'slot' => $slot,
            'form' => $form->createView(),
        ]);
    }

}

<?php

namespace App\Controller\Dashboard;

use App\Entity\Area;
use App\Form\AreaType;
use App\Repository\AreaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/area")
 */
class AreaController extends AbstractController
{
    /**
     * @Route("/", name="area_index", methods={"GET", "POST"})
     */
    public function index(AreaRepository $areaRepository, Request $request): Response
    {
        $this->get('session')->set('activeMenu', 'areas');

        $area = new Area();
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($area);
            $entityManager->flush();
            $this->addFlash('success', 'A new city has been added');
            return $this->redirectToRoute('area_index');
        }

        return $this->render('area/index.html.twig', [
            'areas' => $areaRepository->findBy([], ['id' => 'DESC']),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="area_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $area = new Area();
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($area);
            $entityManager->flush();

            return $this->redirectToRoute('area_index');
        }

        return $this->render('area/new.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="area_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Area $area): Response
    {
        return $this->render('area/show.html.twig', [
            'area' => $area,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="area_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Area $area): Response
    {
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('area_index');
        }

        return $this->render('area/edit.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="area_delete", methods={"GET"})
     */
    public function delete(Area $area): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($area);
        $entityManager->flush();
        $this->addFlash('success', 'The area "' . $area->getNameEn() . '" has been deleted');
        return $this->redirectToRoute('area_index');
    }
}

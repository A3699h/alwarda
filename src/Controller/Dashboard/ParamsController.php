<?php


namespace App\Controller\Dashboard;


use App\Entity\Params;
use App\Repository\ParamsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ParamsController
 * @package App\Controller\Dashboard
 * @Route("/params")
 */
class ParamsController extends AbstractController
{

    /**
     * @Route("/",
     *     name="dashboard_list_params")
     */
    public function list(ParamsRepository $paramsRepository)
    {
        $this->get('session')->set('activeMenu', 'params');
        return $this->render('params/params.html.twig', [
            'params' => $paramsRepository->findAll()
        ]);
    }

    /**
     * @Route("/update/{id}",
     *     name="dashboard_update_param",
     *     options={"expose"=true}
     *     )
     */
    public function updateValue(Params $params,
                                Request $request,
                                EntityManagerInterface $manager)
    {
        $value = $request->get('value');
        $params->setValue($value);
        $manager->flush();
        $this->addFlash('success', 'The parameter has been changed.');
        return $this->redirectToRoute('dashboard_list_params');
    }


}

<?php


namespace App\Controller\Dashboard;


use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BlogController
 * @package App\Controller\Dashboard
 * @Route("/blog")
 */
class BlogController extends AbstractController
{

    /**
     * @Route("/", name="list_blogs")
     */
    public function list(BlogRepository $blogRepository)
    {
        $this->get('session')->set('activeMenu', 'blog');
        return $this->render('blog/list.html.twig', [
            'blogs' => $blogRepository->findAll()
        ]);
    }

    /**
     * @Route("/add", name="add_blog_article")
     */
    public function add(Request $request,
                        EntityManagerInterface $manager)
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($blog);
            $manager->flush();
            $this->addFlash('success', 'The article has been successfully added.');
            return $this->redirectToRoute('list_blogs');
        }
        return $this->render('blog/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_blog_article")
     */
    public function edit(Blog $blog,
                         Request $request,
                         EntityManagerInterface $manager)
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'The article has been successfully updated.');
            return $this->redirectToRoute('list_blogs');
        }
        return $this->render('blog/new.html.twig', [
            'form' => $form->createView(),
            'article' => $blog
        ]);
    }

    /**
     * @param Blog $blog
     * @param EntityManagerInterface $manager
     * @Route("delete/{id}", name="delete_blog")
     */
    public function delete(Blog $blog,
                           EntityManagerInterface $manager)
    {
        $manager->remove($blog);
        $manager->flush();
        $this->addFlash('success', 'The article has been successfully deleted.');
        return $this->redirectToRoute('list_blogs');
    }

}

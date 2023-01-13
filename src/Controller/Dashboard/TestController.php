<?php


namespace App\Controller\Dashboard;


use App\Kernel;
use App\Repository\MessageFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Factory\QrCodeFactory;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TestController extends AbstractController
{

    /**
     * @Route("/test", name="test_route")
     */
    public function test(QrCodeFactory $factory, KernelInterface $kernel, MessageFileRepository $messageFileRepository, UrlGeneratorInterface $router, EntityManagerInterface $em)
    {
        // Creating And storing a Qrcode for a file
        // TODO: Transfer the code to a proper service or event Subscriber to create the code after flushing the file in the database
        // TODO: create a route to return the file from given ID
        $file = $messageFileRepository->findOneByFile('test.mp3');
        $route = $router->generate('area_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $route .= $file->getId();

        $code = $factory->create($route);
        $codeName = uniqid($file->getId()) . '.png';
        $codeName = $file->setQrCode($codeName);
        $em->persist($file);
        $em->flush();
        $code->writeFile($kernel->getProjectDir() . '/public/qrCodes/' . $codeName);

        return new QrCodeResponse($code);
    }
}

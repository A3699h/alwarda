<?php


namespace App\Controller\Dashboard;


use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Service\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{

    /**
     * @Route("/messages", name="message_list")
     */
    public function messagesList(MessageRepository $messageRepository)
    {
        $messages = $messageRepository->findAll();
        $discussions = [];
        foreach ($messages as $message) {
            if ($message->getSender() && $message->getSender()->getRole() == User::USER_ROLES['client']) {
                $discussions[$message->getSender()->getId()][] = $message;
            } elseif ($message->getReciever() && $message->getReciever()->getRole() == User::USER_ROLES['client']) {
                $discussions[$message->getReciever()->getId()][] = $message;
            }
        }
        return $this->render('message/index.html.twig', [
            'discussions' => $discussions
        ]);
    }

    /**
     * @Route("/discussion/{id}", name="show_discussion")
     */
    public function showDiscussion(User $user,
                                   MessageRepository $messageRepository,
                                   EntityManagerInterface $manager)
    {
        $messages = $messageRepository->findDiscussion($user);
        foreach ($messages as $message) {
            if ($message->getNew() && $message->getSender() && $message->getSender()->getRole() == User::USER_ROLES['client']) {
                $message->setNew(false);
                $manager->persist($message);
            }
        }
        $manager->flush();

        return $this->render('message/show.html.twig', [
            'messages' => $messages,
            'client' => $user
        ]);
    }

    /**
     * @Route("/message/add/{id}", name="add_message")
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param Request $request
     */
    public function addMessage(User $user,
                               EntityManagerInterface $manager,
                               Request $request,
                               UtilsService $utilsService)
    {
        $message = new Message();
        $msg = $request->get('message');
        $message->setMessage($msg)
            ->setSender($this->getUser())
            ->setNew(true)
            ->setReciever($user);
        $manager->persist($message);
        $manager->flush();
        $utilsService->sendMsgNotification($user->getId(), $message);
        return $this->redirectToRoute('show_discussion', [
            'id' => $user->getId()
        ]);

    }

}

<?php


namespace App\Controller\Api;


use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use App\Entity\Message;

class ApiMessageController extends AbstractApiController
{

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_client_send_message",
     *     path="/client/message",
     *     defaults={"_api_resource_class"=Message::class, "_api_collection_operation_name"="messages"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Send new message to admins"
     * )
     * @SWG\Tag(name="Messages")
     * @Security(name="Bearer")
     *
     */
    public function sendMessage(Request $request, EntityManagerInterface $manager)
    {
        $submittedData = json_decode($request->getContent());
        $msg = $submittedData->message;

        $message = new Message();
        $message->setMessage($msg);
        $message->setSender($this->getUser());
        $message->setNew(true);

        $manager->persist($message);
        $manager->flush();


        return $this->json($message, Response::HTTP_OK, [], ["default"]);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_client_get_messages",
     *     path="/client/messages",
     *     defaults={"_api_resource_class"=Message::class, "_api_collection_operation_name"="messages"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the client discussion"
     * )
     * @SWG\Tag(name="Messages")
     * @Security(name="Bearer")
     *
     */
    public function getMessages(EntityManagerInterface $manager, MessageRepository $messageRepository)
    {
        $messages = $messageRepository->findDiscussion($this->getUser());
        $res = [];
        foreach ($messages as $message) {
            $res[] = clone $message;
            if ($message->getNew() && $message->getReciever() && $message->getReciever()->getRole() == User::USER_ROLES['client']) {
                $message->setNew(false);
                $manager->persist($message);
            }
        }
        $manager->flush();

        return $this->json($res, Response::HTTP_OK, [], ["default"]);
    }
}

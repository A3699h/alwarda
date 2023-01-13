<?php


namespace App\EventSubscriber;


use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Events;

class ApiLoginSuccessSubscriber implements EventSubscriberInterface
{
    private $repo;
    private $jms;

    public function __construct(UserRepository $repo, SerializerInterface $jms)
    {
        $this->repo = $repo;
        $this->jms = $jms;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'addUserToRequest'
        ];
    }

    public function addUserToRequest(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();
        if ($user->getRole() == User::USER_ROLES['driver'] || $user->getRole() == User::USER_ROLES['client']) {
            if ($user->getRole() == User::USER_ROLES['driver']) {
                $context = SerializationContext::create()->setGroups(['driver']);
            } elseif ($user->getRole() == User::USER_ROLES['client']) {
                $context = SerializationContext::create()->setGroups(['basicClient']);
            }
            $userSerialized = $this->jms->serialize($user, 'json', $context);

            $data = $event->getData();
            $data['user'] = json_decode($userSerialized);
            $event->setData($data);
        }
    }
}

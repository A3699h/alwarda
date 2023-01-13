<?php


namespace App\Controller\Api;


use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     */
    protected function json($data, int $status = 200, array $headers = [], array $groups = []): JsonResponse
    {
        if (is_string($data)) {
            $data = [
                'status_code' => $status,
                'status' => Response::$statusTexts[$status],
                'response' => $data
            ];
        }
        $context = count($groups) > 0 ? SerializationContext::create()->setGroups($groups) : SerializationContext::create();
        if ($this->container->has('serializer')) {
            $json = $this->serializer->serialize($data, 'json', $context);

            return new JsonResponse($json, $status, $headers, true);
        }

        return new JsonResponse($data, $status, $headers);
    }


}

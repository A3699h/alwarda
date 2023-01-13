<?php


namespace App\Service;


use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{

    private $paymentId;
    private $apiPersistToken;
    private $paymentSecretKey;
    private $client;
    private $orderRepository;
    private $manager;

    private $token;

    public function __construct($paymentApiId,
                                $apiPersistToken,
                                $paymentSecretKey,
                                EntityManagerInterface $manager,
                                OrderRepository $orderRepository)
    {
        $this->paymentId = $paymentApiId;
        $this->apiPersistToken = $apiPersistToken;
        $this->paymentSecretKey = $paymentSecretKey;
        $this->client = new Client();
        $this->orderRepository = $orderRepository;
        $this->manager = $manager;
    }

    public function login()
    {
        $loginData = [
            "apiId" => $this->paymentId,
            "persistToken" => $this->apiPersistToken === "true" ? true : false,
            "secretKey" => $this->paymentSecretKey
        ];
        try {
            $response = $this->client->request('POST', "https://restapi.paylink.sa/api/auth", [
                'json' => $loginData
            ]);
            $token = json_decode($response->getBody()->getContents(), true)['id_token'];
            $this->token = $token;
            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function addInvoice(Order $order)
    {
        if (!$this->login()) {
            return ["error in payment api login", Response::HTTP_INTERNAL_SERVER_ERROR];
        };
        $apiUrl = 'https://restapi.paylink.sa/api/addInvoice';
        $orderProducts = [];
        foreach ($order->getOrderDetails() as $detail) {
            $orderProducts[] = [
                "title" => $detail->getProduct()->getName(),
                "price" => $detail->getPrice(),
                "qty" => $detail->getQuantity(),
                "description" => $detail->getProduct()->getDescription(),
                "imageSrc" => $detail->getProduct()->getImages()[0]->getImage()
            ];
        }
        $body = [
            "amount" => $order->getTotalPrice(),
            "callBackUrl" => "https://alwarda.sa/",
            "clientEmail" => $order->getClient()->getEmail(),
            "clientMobile" => $order->getClient()->getPhone(),
            "clientName" => $order->getClient()->getFullName(),
            "note" => "",
            "orderNumber" => $order->getReference(),
            "products" => $orderProducts
        ];

        try {
            $response = $this->client->request('POST', $apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ],
                'json' => $body
            ]);
            return [json_decode($response->getBody()->getContents(), true), Response::HTTP_OK];
        } catch (GuzzleException $e) {
            return [$e->getMessage(), Response::HTTP_BAD_REQUEST];
        }
    }

    public function getInvoice($transaction)
    {
        if (!$this->login()) {
            return ["error in payment api login", Response::HTTP_INTERNAL_SERVER_ERROR];
        };
        $apiUrl = 'https://restapi.paylink.sa/api/getInvoice/' . $transaction;
        try {
            $response = $this->client->request('GET', $apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ]
            ]);
            $orderStatus = json_decode($response->getBody()->getContents(), true)['orderStatus'];

            $order = $this->orderRepository->findOneBy(['paymentTransaction' => $transaction]);
            if (!$order) {
                return ['Transaction No not valid', Response::HTTP_BAD_REQUEST];
            }

            if ($orderStatus === 'Paid') {
                $order->setPaymentStatus('paid');
                $order->setPaymentDate(new \DateTime());
                $order->setPaymentMethod('visa');
                $this->manager->persist($order);
                $this->manager->flush();
                return ['Order paid', Response::HTTP_OK];
            }
            return [$response, Response::HTTP_FORBIDDEN];
        } catch (GuzzleException $e) {
            return [$e->getMessage(), Response::HTTP_BAD_REQUEST];
        }
    }
}

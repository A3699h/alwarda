<?php


namespace App\Controller\Api;


use App\Entity\User;
use App\Form\DriverType;
use App\Form\UserType;
use App\Repository\AreaRepository;
use App\Repository\UserRepository;
use App\Service\ApiFormError;
use App\Service\UtilsService;
use ArtoxLab\Bundle\SmsBundle\Service\ProviderManager;
use ArtoxLab\Bundle\SmsBundle\Sms\Sms;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ApiUserController extends AbstractApiController
{
    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_current_client",
     *     path="/client/profile",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="profile"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the current client profile",
     *     @Model(type=User::class, groups={"client"})
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     *
     */
    public function getCurrentClient()
    {
        if ($this->getUser()->getRole() == User::USER_ROLES['client']) {
            return $this->json($this->getUser(), 200, [], ['client']);
        }
        return $this->json('The current user is not a client', 404);
    }

    /**
     * @Route(
     *     methods={"GET"},
     *     name="api_current_driver",
     *     path="/driver/profile",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="profile"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the current driver profile",
     *     @Model(type=User::class, groups={"driver"})
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     *
     */
    public function getCurrentDriver(EntityManagerInterface $em)
    {
        $driver = $this->getUser();
        $driver->setLastVisit(new \DateTime());
        $em->persist($driver);
        $em->flush();
        if ($driver->getRole() == User::USER_ROLES['driver']) {
            return $this->json($driver, 200, [], ['driver']);
        }
        return $this->json('The current user is not a driver', 404);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_register_client",
     *     path="/register/client",
     *     options={"expose"=true},
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="register"}
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Register New Client",
     *     @Model(type=User::class, groups={"client"})
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function registerClient(Request $request,
                                   ApiFormError $apiFormError,
                                   UserPasswordEncoderInterface $encoder,
                                   ValidatorInterface $validator,
                                   EntityManagerInterface $em,
                                   UtilsService $utilsService)
    {
        $user = new User();
	echo $user;
        $user->setRole(User::USER_ROLES['unverified']);
        $user->setPlainRole(User::USER_ROLES['client']);
        $form = $this->createForm(UserType::class, $user, [
            'method' => 'POST',
            'csrf_protection' => false
        ]);
        $form->handleRequest($request);
        $form->submit($request->request->all());
        $validateEmail = $validator->validate($user->getPassword(), [new NotBlank()]);
        if (count($validateEmail) > 0) {
            $form->addError(new FormError('Password : ' . $validateEmail[0]->getMessage()));
        }
        if (!$form->isValid()) {
            return $apiFormError->jsonResponseFormError($form);
        }
        $firstPassword = $request->request->get('password')['first'];
        $secondPassword = $request->request->get('password')['second'];
        if ($firstPassword && $secondPassword && $firstPassword !== '' && $secondPassword !== '') {
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
        }
        $user->setActive(true);
        $em->persist($user);
        $em->flush();

        $sendSms = $utilsService->sendVerificationCode($user->getPhone());
        if (array_key_exists('success', $sendSms)) {
            $verificationCode = $sendSms['success'];
            $user->setVerificationCode($verificationCode);
            $em->flush();
            return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
        } else {
            $error = $sendSms['error'];
            return $this->json($error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_resend_verification_code",
     *     path="/resend-verification-code",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="resend"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Resend verification code by SMS in case of error"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function resendVerificationCode(UtilsService $utilsService,
                                           Request $request,
                                           UserRepository $userRepository,
                                           EntityManagerInterface $manager)
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        if ($phone) {
            $user = $userRepository->findOneByPhone($phone);
            if ($user) {
                $sendSms = $utilsService->sendVerificationCode($user->getPhone());
                if (array_key_exists('success', $sendSms)) {
                    $verificationCode = $sendSms['success'];
                    $user->setVerificationCode($verificationCode);
                    $manager->persist($user);
                    $manager->flush();
                    return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
                } else {
                    $error = $sendSms['error'];
                    return $this->json($error, Response::HTTP_BAD_REQUEST);
                }
            }
            return $this->json('No user found for the phone number ' . $phone, Response::HTTP_NOT_FOUND);
        }
        return $this->json('Attribute phone not found', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_register_driver",
     *     path="/register/driver",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="register"}
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Register New Driver",
     *     @Model(type=User::class, groups={"driver"})
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function registerDriver(Request $request,
                                   ApiFormError $apiFormError,
                                   UserPasswordEncoderInterface $encoder,
                                   ValidatorInterface $validator,
                                   EntityManagerInterface $em,
                                   UtilsService $utilsService)
    {
        $user = new User();
        $user->setRole(User::USER_ROLES['unverified']);
        $user->setPlainRole(User::USER_ROLES['driver']);
        $user->setAccessId($utilsService->generateRandomDigitsCode(6));
        $form = $this->createForm(DriverType::class, $user, [
            'method' => 'POST',
            'csrf_protection' => false
        ]);
        $form->handleRequest($request);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $apiFormError->jsonResponseFormError($form);
        }
        $user->setPassword($encoder->encodePassword($user, $user->getAccessId()));
        $user->setActive(true);
        $em->persist($user);
        $em->flush();

        $sendSms = $utilsService->sendVerificationCode($user->getPhone());
        if (array_key_exists('success', $sendSms)) {
            $verificationCode = $sendSms['success'];
            $user->setVerificationCode($verificationCode);
            $em->flush();
            return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
        } else {
            $error = $sendSms['error'];
            return $this->json($error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_update_client",
     *     path="/client/update",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="update"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Update client profile",
     *     @Model(type=User::class, groups={"client"})
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     *
     */
    public function updateClient(Request $request,
                                 ApiFormError $apiFormError,
                                 UserPasswordEncoderInterface $encoder,
                                 EntityManagerInterface $em,
                                 UtilsService $utilsService)
    {
        $user = $this->getUser();
        $oldPhone = $this->getUser()->getPhone();
        $form = $this->createForm(UserType::class, $user, [
            'method' => 'PUT',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
        $submittedData = $request->request->all();
        $submittedData = array_filter($submittedData, function ($el) {
            return $el != '';
        });
        $oldPassword = $request->get('old_password') ?? null;
        if (!$oldPassword) {
            return $this->json("Old password must be specified", Response::HTTP_BAD_REQUEST);
        }
        if (!password_verify($oldPassword, $user->getPassword())) {
            return $this->json("Old password is incorrect", Response::HTTP_UNAUTHORIZED);
        }
        $form->handleRequest($request);
        $form->submit($submittedData);
        if (!$form->isValid()) {
            return $apiFormError->jsonResponseFormError($form);
        }
        $firstPassword = $submittedData['password']['first'] ?? '';
        $secondPassword = $submittedData['password']['second'] ?? '';
        if ($firstPassword !== '' && $secondPassword !== '') {
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
        }
        if ($submittedData['phone'] != $oldPhone) {
            $user->setRole(User::USER_ROLES['unverified']);
            $sendSms = $utilsService->sendVerificationCode($user->getPhone());
            if (array_key_exists('success', $sendSms)) {
                $verificationCode = $sendSms['success'];
                $user->setVerificationCode($verificationCode);
                $em->flush();
                return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
            } else {
                $error = $sendSms['error'];
                return $this->json($error, Response::HTTP_BAD_REQUEST);
            }
        }
        $em->persist($user);
        $em->flush();
        return $this->json($user, 200, [], ['client']);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_update_driver",
     *     path="/driver/update",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="update"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Update driver profile",
     *     @Model(type=User::class, groups={"driver"})
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     *
     */
    public function updateDriver(Request $request,
                                 ApiFormError $apiFormError,
                                 UserPasswordEncoderInterface $encoder,
                                 EntityManagerInterface $em,
                                 AreaRepository $areaRepository,
                                 UtilsService $utilsService)
    {
        $user = $this->getUser();
        $submittedData = $request->request->all();
        if (array_key_exists('fullName', $submittedData)) {
            $user->setFullName($submittedData['fullName']);
        }
        if (array_key_exists('area', $submittedData)) {
            $user->setArea($areaRepository->find($submittedData['area']));
        }
        if (array_key_exists('phone', $submittedData)) {
            $user->setPhone($submittedData['phone']);
            $user->setRole(User::USER_ROLES['unverified']);

            $sendSms = $utilsService->sendVerificationCode($user->getPhone());
            if (array_key_exists('success', $sendSms)) {
                $verificationCode = $sendSms['success'];
                $user->setVerificationCode($verificationCode);
                $em->flush();
                return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
            } else {
                $error = $sendSms['error'];
                return $this->json($error, Response::HTTP_BAD_REQUEST);
            }
        }

        $em->persist($user);
        $em->flush();
        return $this->json($user, 200, [], ['driver']);
    }


    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_verify_user",
     *     options={"expose"=true},
     *     path="/verify",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="verify"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Verify new registred Client or Driver"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function verifyUser(Request $request,
                               UserRepository $userRepository,
                               EntityManagerInterface $manager,
                               JWTTokenManagerInterface $tokenManager,
                               EventDispatcherInterface $eventDispatcher,
                               UtilsService $utilsService)
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        $verificationCode = $data['verification_code'] ?? null;
        if ($phone && $verificationCode) {
            $user = $userRepository->findOneByPhone($phone);
            if ($user) {
                $userStoredCode = $user->getVerificationCode();
                if ($verificationCode == $userStoredCode) {
                    $user->setRole($user->getPlainRole());
                    $user->setVerificationCode(null);
                    if ($user->getRole() == User::USER_ROLES['driver']) {
                        $utilsService->sendDriverPassword($user->getPhone(), $user->getAccessId());
                        $user->setAccessId(null);
                    }
                    $manager->persist($user);
                    $manager->flush();

                    if ($user->getRole() == User::USER_ROLES['driver']) {
                        return $this->json('Password was send to ' . $user->getPhone(), Response::HTTP_OK);
                    }
                    // return $this->json('User verified', Response::HTTP_OK);
                    $jwt = $tokenManager->create($user);
                    $response = new JWTAuthenticationSuccessResponse($jwt);

                    $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
                    $eventDispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);
                    $response->setData($event->getData());

                    return $response;
                }
                return $this->json('The verification code is not valid', Response::HTTP_UNAUTHORIZED);

            }
            return $this->json('No user found for the phone number ' . $phone, Response::HTTP_NOT_FOUND);
        }
        return $this->json('Attribute phone or verification_code not found', Response::HTTP_BAD_REQUEST);
    }


    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_ask_reset_password",
     *     path="/reset-password/ask",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="password"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="First step of reset password => send verification code"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function askResetPassword(Request $request,
                                     UserRepository $userRepository,
                                     EntityManagerInterface $manager,
                                     UtilsService $utilsService)
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        if ($phone) {
            $user = $userRepository->findOneByPhone($phone);
            if ($user) {
                $sendSms = $utilsService->sendVerificationCode($user->getPhone());
                if (array_key_exists('success', $sendSms)) {
                    $verificationCode = $sendSms['success'];
                    $user->setVerificationCode($verificationCode);
                    $user->setRole(User::USER_ROLES['unverified']);
                    $manager->persist($user);
                    $manager->flush();
                    return $this->json('Verification code was send to ' . $user->getPhone(), Response::HTTP_OK);
                } else {
                    $error = $sendSms['error'];
                    return $this->json($error, Response::HTTP_BAD_REQUEST);
                }
            }
            return $this->json('No user found for the phone number ' . $phone, Response::HTTP_NOT_FOUND);
        }
        return $this->json('Attribute phone  not found', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route(
     *     methods={"POST"},
     *     name="api_validate_reset_password",
     *     path="/reset-password/validate",
     *     defaults={"_api_resource_class"=User::class, "_api_collection_operation_name"="password"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Second step of reset password => Verify code and reset password"
     * )
     * @SWG\Tag(name="User")
     *
     */
    public function validateResetPassword(Request $request,
                                          UserRepository $userRepository,
                                          EntityManagerInterface $manager,
                                          UserPasswordEncoderInterface $encoder,
                                          UtilsService $utilsService)
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        $verificationCode = $data['verification_code'] ?? null;
        $password = $data['password'] ?? null;
        $repeaPassword = $data['repeat_password'] ?? null;
        if ($phone && $verificationCode) {
            $user = $userRepository->findOneByPhone($phone);
            if ($user) {
                $userStoredCode = $user->getVerificationCode();
                if ($verificationCode == $userStoredCode) {
                    if ($user->getPlainRole() != User::USER_ROLES['driver'] && (!$password || !$repeaPassword)) {
                        return $this->json('Passwords not found', Response::HTTP_BAD_REQUEST);
                    }
                    $newPassword = $utilsService->generateRandomDigitsCode(6);
                    if ($user->getPlainRole() == User::USER_ROLES['driver']) {
                        $password = $newPassword;
                        $repeaPassword = $newPassword;
                    }
                    if ($password == $repeaPassword) {
                        $user->setRole($user->getPlainRole());
                        $user->setVerificationCode(null);
                        $user->setPassword($encoder->encodePassword($user, $password));
                        $manager->persist($user);
                        $manager->flush();
                        if ($user->getRole() == User::USER_ROLES['driver']) {
                            $utilsService->sendDriverPassword($user->getPhone(), $password);
                            return $this->json('Password was send to ' . $user->getPhone(), Response::HTTP_OK);
                        }
                        return $this->json('Password has been updated', Response::HTTP_OK);
                    }
                    return $this->json('Passwords not matching', Response::HTTP_BAD_REQUEST);
                }
                return $this->json('The verification code is not valid', Response::HTTP_UNAUTHORIZED);
            }
            return $this->json('No user found for the phone number ' . $phone, Response::HTTP_NOT_FOUND);
        }
        return $this->json('Attribute phone or verification_code or password or repeat_password  not found', Response::HTTP_BAD_REQUEST);
    }

}

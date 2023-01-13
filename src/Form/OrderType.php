<?php

namespace App\Form;

use App\Entity\DeliveryAddress;
use App\Entity\DiscountVoucher;
use App\Entity\MessageFile;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Slot;
use App\Entity\User;
use App\Repository\DeliveryAddressRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class OrderType extends AbstractType
{

    private $deliveryAddressRepository;
    private $userRepository;

    public function __construct(DeliveryAddressRepository $deliveryAddressRepository,
                                UserRepository $userRepository)
    {
        $this->deliveryAddressRepository = $deliveryAddressRepository;
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new \DateTime();
        $builder
            ->add('deliveryDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'constraints' => [
                    new GreaterThanOrEqual($today->format('Y-m-d'))
                ]
            ])
            ->add('shop', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.role >= :shopRole')
                        ->setParameter('shopRole', User::USER_ROLES['shop']);
                },
                'choice_label' => function (User $shop) {
                    return $shop->getFullName() . ' -' . ($shop->getArea() ? $shop->getArea()->getNameEn() : '');
                },
                'placeholder' => 'Not assigned',
                'required' => false,
                'attr' => [
                    'class' => 'products-basic-select2'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Order Status',
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, array_values(Order::ORDER_STATUS)), array_values(Order::ORDER_STATUS)),
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, array_values(Order::ORDER_PAYMENT_STATUS)), array_values(Order::ORDER_PAYMENT_STATUS)),
            ])
            ->add('paymentDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'constraints' => [
                    new GreaterThanOrEqual($today->format('Y-m-d'))
                ]
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, array_values(Order::ORDER_PAYMENT_METHODS)), array_values(Order::ORDER_PAYMENT_METHODS)),
            ])
            ->add('VAT', NumberType::class, [
                'label' => 'VAT (%)'
            ])
            ->add('messageFrom', TextType::class, [
                'required' => false
            ])
            ->add('messageTo', TextType::class, [
                'required' => false
            ])
            ->add('message', TextareaType::class, [
                'required' => false
            ])
            ->add('messageLink', TextType::class, [
                'required' => false
            ])
            ->add('discountVoucher', EntityType::class, [
                'class' => DiscountVoucher::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('dv')
                        ->where('dv.endDate >= :today')
                        ->andWhere('dv.startDate <= :today')
                        ->setParameter('today', new \DateTime());
                },
                'choice_label' => function (DiscountVoucher $voucher) {
                    return $voucher->getCode() . ': -' . $voucher->getDiscountPercentage() . ' %';
                },
                'placeholder' => 'No voucher',
                'required' => false
            ])
            ->add('deliverySlot', EntityType::class, [
                'class' => Slot::class,
                'choice_label' => function (Slot $slot) {
                    return $slot->getName() . ': From ' . $slot->getDeliveryAt()->format('H:i') . ' To ' . $slot->getDeliveryTo()->format('H:i');
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->leftJoin('s.orders', 'o')
                        ->andWhere('s.active = :trueVal')
                        ->having('COUNT(o.id) < s.maxOrders')
                        ->setParameter('trueVal', true)
                        ->groupBy('s');
                },
            ])
            ->add('notes', CollectionType::class, [
                'label' => false,
                'required' => false,
                'entry_type' => OrderNoteType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('orderDetails', CollectionType::class, [
                'label' => false,
                'entry_type' => OrderDetailType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('hideSender',CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'false_values' => [false, 'false', 'FALSE', 0, '0', null]
            ])
            ->add('messageFile', MessageFileType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'))
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));;
    }


    protected function addElements(FormInterface $form, User $client = null)
    {
        $form->add('client', EntityType::class, [
            'class' => User::class,
            'data' => $client,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.role = :clientRole')
                    ->setParameter('clientRole', User::USER_ROLES['client']);
            },
            'choice_label' => function (User $client) {
                return $client->getFullName();
            },
            'attr' => [
                'class' => 'user-basic-select2'
            ]
        ]);

        $deliveryAddress = [];
        if ($client) {
            $deliveryAddress = $this->deliveryAddressRepository->createQueryBuilder("da")
                ->where("da.client = :client")
                ->setParameter("client", $client)
                ->getQuery()
                ->getResult();
        }

        $form->add('deliveryAddress', EntityType::class, [
            'required' => true,
            'placeholder' => 'Select a client first ...',
            'class' => DeliveryAddress::class,
            'choices' => $deliveryAddress,
            'choice_label' => function (DeliveryAddress $address) {
                return $address->getId();
            }
        ]);
    }


    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $client = $this->userRepository->find($data['client']);
        $this->addElements($form, $client);
    }

    function onPreSetData(FormEvent $event)
    {
        $order = $event->getData();
        $form = $event->getForm();

        $client = $order->getClient() ? $order->getClient() : null;

        $this->addElements($form, $client);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}

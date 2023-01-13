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

class ApiOrderType extends AbstractType
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
                'constraints' => [
                    new GreaterThanOrEqual($today->format('Y-m-d'))
                ]
            ])
            ->add('VAT')
            ->add('messageFrom')
            ->add('messageTo')
            ->add('message')
            ->add('messageLink')
            ->add('deliverySlot', EntityType::class, [
                'class' => Slot::class,
            ])
            ->add('orderDetails', CollectionType::class, [
                'label' => false,
                'entry_type' => OrderDetailType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('messageFile', MessageFileType::class)
            ->add('deliveryAddress', EntityType::class, [
                'required' => true,
                'class' => DeliveryAddress::class
            ])
            ->add('orderOrigin');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ]);
    }
}

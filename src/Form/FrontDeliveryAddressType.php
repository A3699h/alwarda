<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\DeliveryAddress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class FrontDeliveryAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recieverName', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Name'
                ]
            ])
            ->add('recieverPhone', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Phone Number'
                ]
            ])
            ->add('recieverFullAddress', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'label' => false,
                'attr' => [
                    'placeholder' => 'Full Address'
                ],
                'required' => true
            ])
            ->add('recieverArea', EntityType::class, [
                'label' => false,
                'required' => true,
                'class' => Area::class,
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => [
                    'placeholder' => 'City'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryAddress::class,
        ]);
    }
}

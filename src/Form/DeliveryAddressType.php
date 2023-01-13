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

class DeliveryAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recieverName', TextType::class, [
                'required' => false
            ])
            ->add('recieverPhone', TextType::class, [
                'required' => false
            ])
            ->add('recieverFullAddress', TextType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('recieverLocations', TextType::class, [
                'required' => false
            ])
            ->add('notes', TextType::class, [
                'required' => false
            ])
            ->add('recieverArea', EntityType::class, [
                'label' => 'Reciever City',
                'class' => Area::class,
                'choice_label' => function (Area $area) {
                    return $area->getId();
                },
                'constraints' => [
                    new NotBlank()
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

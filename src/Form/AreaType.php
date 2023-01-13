<?php

namespace App\Form;

use App\Entity\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nameEn', TextType::class, [
                'label' => 'Name (English))'
            ])
            ->add('nameAr', TextType::class, [
                'label' => 'Name (Arabic)'
            ])
            ->add('active', CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('mapName', ChoiceType::class, [
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, array_values(Area::MAP_NAMES)), array_values(Area::MAP_NAMES)),
            ])
            ->add('deliveryPrice', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
        ]);
    }
}

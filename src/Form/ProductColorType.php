<?php

namespace App\Form;

use App\Entity\ProductColor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductColorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('color', TextType::class, [
                'label' => 'Name'
            ])
            ->add('colorAr', TextType::class, [
                'label' => 'Name (arabic)'
            ])
            ->add('code', ColorType::class, [
                'label' => 'Color',
                'attr' => [
                    'pattern' => '/^#[0-9a-f]{6}$/i',
                    'style' => 'font-size:2rem;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductColor::class,
        ]);
    }
}

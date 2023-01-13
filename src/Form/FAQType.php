<?php

namespace App\Form;

use App\Entity\FAQ;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FAQType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question')
            ->add('questionAr', TextType::class, [
                'label' => 'Question (arabic)'
            ])
            ->add('answer', TextareaType::class, [
                'attr' => [
                    'rows' => "20"
                ]
            ])
            ->add('answerAr', TextareaType::class, [
                'label' => 'Answer (arabic)',
                'attr' => [
                    'rows' => "20"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FAQ::class,
        ]);
    }
}

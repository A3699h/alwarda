<?php

namespace App\Form;

use App\Entity\Policy;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('privacyEn', CKEditorType::class, [
                'label'=> 'Privacy Policy (English)'
            ])
            ->add('privacyAr', CKEditorType::class, [
                'label'=> 'Privacy Policy (Arabic)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Policy::class,
        ]);
    }
}

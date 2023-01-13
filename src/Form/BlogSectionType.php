<?php

namespace App\Form;

use App\Entity\BlogSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlogSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'required' => true,
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('contentAr', TextareaType::class, [
                'label' => 'Content  (arabic)',
                'required' => true,
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'image',
                'allow_delete' => false,
                'required' => false,
                'download_link' => false,
                'download_uri' => false,
                'constraints' => [
                    new Image()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlogSection::class,
        ]);
    }
}

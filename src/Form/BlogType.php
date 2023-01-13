<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => true
            ])
            ->add('titleAr', TextType::class, [
                'label' => 'Title (arabic)',
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Short description',
                'required' => true
            ])
            ->add('descriptionAr', TextareaType::class, [
                'label' => 'Short description  (arabic)',
                'required' => true
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Main Image',
                'allow_delete' => false,
                'required' => false,
                'download_link' => false,
                'download_uri' => false,
                'constraints' => [
                    new Image()
                ]
            ])
            ->add('published', CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('sections', CollectionType::class, [
                'label' => false,
                'entry_type' => BlogSectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductColor;
use App\Entity\ProductImage;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('SKU', TextType::class, [
                'label' => 'SKU'
            ])
            ->add('designedBy', TextType::class, [
                'label' => 'Designed By'
            ])
            ->add('name')
            ->add('nameAr', TextType::class, [
                'label'=> 'Name (arabic)'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, Product::PRODUCT_TYPES), Product::PRODUCT_TYPES)
            ])
            ->add('color', EntityType::class, [
                'class' => ProductColor::class,
                'choice_label' => function (ProductColor $color) {
                    return $color->getColor();
                },
                'attr' => [
                    'class' => 'basic-select2'
                ]
            ])
            ->add('cost', NumberType::class, [
                'label' => 'Cost (SAR)',
                'constraints' => [
                    new PositiveOrZero()
                ]
            ])
            ->add('benefit', NumberType::class, [
                'label' => 'Profit (SAR)',
                'constraints' => [
                    new PositiveOrZero()
                ]
            ])
            ->add('description')
            ->add('descriptionAr', TextareaType::class, [
                'label'=> 'Description (arabic)'
            ])
            ->add('enabled', CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible on store',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('package', CheckboxType::class, [
                'label' => 'This product is a package',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('discountable', CheckboxType::class, [
                'label' => 'Accept discounts',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('longDescription', CKEditorType::class)
            ->add('longDescriptionAr', CKEditorType::class)
            ->add('category')
            ->add('images', CollectionType::class, [
                'label' => false,
                'entry_type' => ProductImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

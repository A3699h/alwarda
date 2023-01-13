<?php

namespace App\Form;

use App\Entity\OrderDetail;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class OrderDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->innerJoin('p.users', 'u')
                        ->where('p.enabled = :trueVal')
                        ->setParameter('trueVal', true);
                },
                'choice_label' => function (Product $product) {
                    return $product->getSKU() . ' - ' . $product->getName();
                },
                'attr' => [
                    'class' => 'products-basic-select2'
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Positive()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderDetail::class,
        ]);
    }
}

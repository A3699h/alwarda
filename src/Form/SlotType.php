<?php

namespace App\Form;

use App\Entity\Slot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('maxOrders', IntegerType::class, [
                'label' => 'Maximum orders (0 means unlimited)'
            ])
            ->add('showAt', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker', 'autocomplete' => 'off'],
            ])
            ->add('closeAt', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker', 'autocomplete' => 'off'],
            ])
            ->add('deliveryAt', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker', 'autocomplete' => 'off'],
            ])
            ->add('deliveryTo', TimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker', 'autocomplete' => 'off'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(array_map(function ($el) {
                    return ucfirst($el);
                }, array_values(Slot::TYPES)), array_values(Slot::TYPES)),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Slot::class,
        ]);
    }
}

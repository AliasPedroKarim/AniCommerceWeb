<?php

namespace App\Form;

use App\Controller\HoraireMagasinController;
use App\Entity\HoraireMagasin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoraireMagasinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $conf = [
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'HH:mm',
            'attr' => ['class' => 'clockpicker'],
            'required' => false
        ];

        $builder
            ->add('jour', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'attr' => [ 'class' => 'custom-select mb-2', 'multiple' => 'true', 'size' => 3 ],
                'choices' => HoraireMagasinController::day,
                'choice_attr' => function($choice, $key, $value) {
                    return ['id' => 'Jour'.$value];
                },
            ])
            ->add('hOuvertureMatin', DateTimeType::class, $conf)
            ->add('hFermetureMatin', DateTimeType::class, $conf)
            ->add('hOuvertureMidi', DateTimeType::class, $conf)
            ->add('hFermetureMidi', DateTimeType::class, $conf)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HoraireMagasin::class,
        ]);
    }
}

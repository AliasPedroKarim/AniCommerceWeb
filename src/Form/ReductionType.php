<?php

namespace App\Form;

use App\Entity\Reduction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReductionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('actif')
            ->add('valeur')
            ->add('idTypeReduction')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reduction::class,
        ]);
    }
}

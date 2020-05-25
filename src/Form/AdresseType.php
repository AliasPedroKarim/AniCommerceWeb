<?php

namespace App\Form;

use App\Entity\Adresse;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('adr')
            ->add('compl')
            ->add('idVille', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function ($ville) {
                    return ucfirst($ville->getLibelle()) . ', ' . ucfirst($ville->getPays());
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}

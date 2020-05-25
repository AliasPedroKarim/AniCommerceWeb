<?php

namespace App\Form;

use App\Entity\Resider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResiderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('id')
            ->add('defaut', CheckboxType::class, [
                'label_attr' => [ 'class' => 'custom-control-label' ],
                'required' => false
            ])
            //->add('idUtilisateur')
            //->add('idAdresse')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resider::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Magasin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagasinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('idHoraireMagasin')
            ->add('nom', TextType::class, [
                'required' => true
            ])
            ->add('telephone', TextType::class, [
                'required' => true
            ])
            ->add('courriel', EmailType::class, [
                'required' => true
            ])
            ->add('latitude', NumberType::class, [
                'required' => false
            ])
            ->add('longitude', NumberType::class, [
                'required' => false
            ])
            // ->add('idImage')
            // ->add('idAdresse')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Magasin::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Genre;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('courriel', EmailType::class)
            ->add('telephone', TextType::class)
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('motDePasse', PasswordType::class)
            ->add('checkPassword', PasswordType::class)
            ->add('idGenre', EntityType::class, [
                'class' => Genre::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('g')->orderBy('g.id', 'ASC');
                },
                'choice_label' => 'libelle'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'required' => false,
            'validation_groups' => ['Register'],
        ]);
    }
}

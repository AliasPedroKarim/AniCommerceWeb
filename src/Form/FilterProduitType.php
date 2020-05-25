<?php

namespace App\Form;

use App\Entity\Categorie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('wordKeys', TextType::class, [
                'required' => false
            ])
            ->add('categories', EntityType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'class' => Categorie::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('c')->orderBy('c.id', 'ASC');
                },
                'choice_label' => 'libelle',
            ])
            ->add('priceMin', NumberType::class, [
                'required' => false,
                'html5' => true
            ])
            ->add('priceMax', NumberType::class, [
                'required' => false,
                'html5' => true
            ])
            // TODO Date d'ajout
            // ->add('priceMax')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

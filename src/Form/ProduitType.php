<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Magasin;
use App\Entity\Produit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'required' => true
            ])
            ->add('prixHt', NumberType::class, [
                'required' => true
            ])
            ->add('stock', NumberType::class, [
                'required' => true
            ])
            ->add('idMagasin', EntityType::class, [
                'class' => Magasin::class,
                'attr' => [ 'class' => 'form-group'],
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('g')->orderBy('g.id', 'ASC');
                },
                'choice_label' => 'nom'
            ])
            ->add('associerCategories', EntityType::class, [
                'class' => Categorie::class,
                'multiple' => true,
                'required' => false,
                'attr' => [ 'class' => 'form-group'],
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
            'data_class' => Produit::class,
        ]);
    }
}

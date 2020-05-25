<?php

namespace App\Form;

use App\Entity\LigneCommande;
use App\Entity\Produit;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('id')
            ->add('quantite', NumberType::class)
            // ->add('prixUnitaire')
            /*->add('idProduit', EntityType::class, [
                'class' => Produit::class,
                'label' => 'libelle'
            ])*/
            /*->add('idCommande')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LigneCommande::class,
        ]);
    }
}

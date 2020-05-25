<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('upload', FileType::class, [
                'label' => "Sélectionner le fichier",
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ taille }} {{ suffixe }}). La taille maximale autorisée est de {{ limite }} {{{suffixe}}.',
                        'mimeTypes' => ['application/json', 'application/xml+html', 'text/xml'],
                        'mimeTypesMessage' => 'Le type mime du fichier n\'est pas valide ({{ type }}). Les types de mime autorisés sont {{ types }}.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([

        ]);
    }
}

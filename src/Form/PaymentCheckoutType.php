<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentCheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return self::build($builder);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @return FormBuilderInterface
     * @throws \Exception
     */
    public static function build(FormBuilderInterface $builder) {
        return $builder
            ->add('firstName', TextType::class, [
                'required' => true
            ])
            ->add('lastName', TextType::class, [
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'required' => true
            ])
            ->add('address', TextType::class, [
                'required' => true
            ])
            ->add('compl', TextType::class, [
                'required' => true
            ])
            ->add('country', TextType::class, [
                'required' => true
            ])
            ->add('state', TextType::class, [
                'required' => true
            ])
            ->add('zip', TextType::class, [
                'required' => true
            ])
            ->add('dateShipping', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'datepicker-shippping'],
            ])
            ->add('sameAddress', CheckboxType::class, [
                'required' => false
            ])
            ->add('saveInfo', CheckboxType::class, [
                'required' => false
            ])
            ->add('paymentMethod', TextType::class, [
                'required' => true
            ])
            ->add('ccName', TextType::class, [
                'required' => false
            ])
            ->add('ccNumber', TextType::class, [
                'required' => false
            ])
            ->add('ccExpiration', TextType::class, [
                'required' => false
            ])
            ->add('ccCvv', TextType::class, [
                'required' => false
            ])
            ;
    }
}

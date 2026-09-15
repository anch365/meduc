<?php

namespace App\Form;

use App\Entity\Etablissement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransfertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etablissement', EntityType::class, [
                'label' => 'Nouvel établissement',
                'class' => Etablissement::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un établissement',
                'constraints' => [new NotBlank()],
            ])
            ->add('dateTransfert', DateType::class, [
                'label' => 'Date du transfert',
                'widget' => 'single_text',
                'constraints' => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // PAS de data_class : ce formulaire ne remplit pas une entité !
        ]);
    }
}
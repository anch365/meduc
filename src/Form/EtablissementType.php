<?php

namespace App\Form;

use App\Entity\Etablissement;
use App\Entity\Localite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => "Nom de l'établissement"])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Primaire' => 'primaire',
                    'Collège' => 'college',
                    'Lycée' => 'lycee',
                ],
            ])
            ->add('localite', EntityType::class, [
                'label' => 'Localité',
                'class' => Localite::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une localité',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
        ]);
    }
}
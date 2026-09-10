<?php

namespace App\Form;

use App\Entity\Enseignant;
use App\Entity\Localite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnseignantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ---- Infos du PROFIL enseignant ----
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => ['Masculin' => 'M', 'Féminin' => 'F'],
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
            ])
            ->add('lieuNaissance', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => false,
            ])
            ->add('niveauEnseignement', ChoiceType::class, [
                'label' => "Niveau d'enseignement",
                'choices' => [
                    'Primaire' => 'primaire',
                    'Secondaire' => 'secondaire',
                    'Supérieur' => 'superieur',
                ],
            ])
            ->add('statutProfessionnel', ChoiceType::class, [
                'label' => 'Statut professionnel',
                'choices' => [
                    'Actif' => 'actif',
                    'En congé' => 'en_conge',
                    'Retraité' => 'retraite',
                ],
            ])
            ->add('localite', EntityType::class, [
                'label' => 'Localité',
                'class' => Localite::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une localité',
                'required' => false,
            ])
            // ---- Infos du COMPTE (relation 1-1) ----
            ->add('utilisateur', UtilisateurType::class, [
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enseignant::class,
        ]);
    }
}
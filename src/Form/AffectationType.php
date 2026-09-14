<?php

namespace App\Form;

use App\Entity\Affectation;
use App\Entity\Enseignant;
use App\Entity\Etablissement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('classe', TextType::class, [
                'label' => 'Classe',
            ])
            ->add('matiere', TextType::class, [
                'label' => 'Matière',
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('enseignant', EntityType::class, [
                'label' => 'Enseignant',
                'class' => Enseignant::class,
                'choice_label' => fn (Enseignant $e) => ($e->getUtilisateur()?->getPrenom() ?? '') . ' ' . ($e->getUtilisateur()?->getNom() ?? '') . ' (' . ($e->getUtilisateur()?->getMatricule() ?? '') . ')',
                'placeholder' => 'Choisir un enseignant',
            ])
            ->add('etablissement', EntityType::class, [
                'label' => 'Établissement',
                'class' => Etablissement::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un établissement',
            ]);
        // ⚠️ PAS de champ dateFin (doit rester NULL = en cours)
        // ⚠️ PAS de champ statut (décidé par le système : 'en_cours')
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Affectation::class,
        ]);
    }
}

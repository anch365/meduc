<?php

namespace App\DataFixtures;

use App\Entity\Localite;
use App\Entity\Utilisateur;
use App\Entity\Etablissement;
use App\Entity\Enseignant;
use App\Entity\Affectation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ===================== 1. LOCALITÉS =====================
        $moroni = new Localite();
        $moroni->setNom('Moroni');
        $moroni->setIle('Grande Comore');
        $manager->persist($moroni);

        $mutsamudu = new Localite();
        $mutsamudu->setNom('Mutsamudu');
        $mutsamudu->setIle('Anjouan');
        $manager->persist($mutsamudu);

        $fomboni = new Localite();
        $fomboni->setNom('Fomboni');
        $fomboni->setIle('Mohéli');
        $manager->persist($fomboni);

        // ===================== 2. UTILISATEURS =====================
        // L'ADMINISTRATEUR (email pour se connecter)
        $admin = new Utilisateur();
        $admin->setEmail('admin@meduc.km');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setMatricule('ADM001');
        $admin->setActif(true);
        $admin->setNom('Admin');
        $admin->setPrenom('MEDUC');
        $manager->persist($admin);

        // 20 enseignants : matricule ENS001 à ENS020, mot de passe commun
        $noms = [
            'Ali',
            'Ahmed',
            'Said',
            'Mohamed',
            'Abdou',
            'Salim',
            'Youssouf',
            'Ibrahim',
            'Bacar',
            'Moussa',
            'Omar',
            'Hamadi',
            'Soilihi',
            'Attoumane',
            'Chamsoudine',
            'Nassur',
            'Anfane',
            'Boina',
            'Mroivili',
            'Djae'
        ];
        $prenoms = [
            'Karim',
            'Fatima',
            'Moussa',
            'Zahra',
            'Abdallah',
            'Nadjat',
            'Said',
            'Halima',
            'Ishak',
            'Achmet',
            'Toiha',
            'Mlaili',
            'Fahari',
            'Ahamada',
            'Chaharane',
            'Zamir',
            'Mikidadi',
            'Rashid',
            'Imani',
            'Sitti'
        ];

        $enseignants = []; // on garde les objets pour les affectations
        for ($i = 1; $i <= 20; $i++) {
            $compte = new Utilisateur();
            $compte->setEmail(sprintf('enseignant%02d@meduc.km', $i));
            $compte->setRoles(['ROLE_ENSEIGNANT']);
            $compte->setPassword($this->hasher->hashPassword($compte, 'enseignant123'));
            $compte->setMatricule(sprintf('ENS%03d', $i));
            $compte->setActif(true);
            $compte->setNom($noms[$i - 1]);
            $compte->setPrenom($prenoms[$i - 1]);
            $manager->persist($compte);

            $enseignants[$i] = $compte; // mémoriser pour créer les profils
        }

        // ===================== 3. ÉTABLISSEMENTS =====================
        $lyceeMoroni = new Etablissement();
        $lyceeMoroni->setNom('Lycée de Moroni');
        $lyceeMoroni->setType('lycee');
        $lyceeMoroni->setLocalite($moroni);
        $lyceeMoroni->setActif(true);
        $manager->persist($lyceeMoroni);

        $lyceeMutsamudu = new Etablissement();
        $lyceeMutsamudu->setNom('Lycée de Mutsamudu');
        $lyceeMutsamudu->setType('lycee');
        $lyceeMutsamudu->setLocalite($mutsamudu);
        $lyceeMutsamudu->setActif(true);
        $manager->persist($lyceeMutsamudu);

        $ecoleFomboni = new Etablissement();
        $ecoleFomboni->setNom('École primaire de Fomboni');
        $ecoleFomboni->setType('primaire');
        $ecoleFomboni->setLocalite($fomboni);
        $ecoleFomboni->setActif(true);
        $manager->persist($ecoleFomboni);

        // ===================== 4. ENSEIGNANTS =====================
        // On relie chaque compte à un profil Enseignant
        $profils = []; // ← nouveau tableau pour les profils
        foreach ($enseignants as $i => $compte) {
            $enseignant = new Enseignant();
            $enseignant->setGenre($i % 2 === 0 ? 'M' : 'F');
            $enseignant->setDateNaissance(new \DateTime('1955-01-15'));
            $enseignant->setNiveauEnseignement('secondaire');
            $enseignant->setStatutProfessionnel('actif');
            $enseignant->setUtilisateur($compte);
            $enseignant->setLocalite($moroni);
            $manager->persist($enseignant);

            $profils[$i] = $enseignant; // ← on garde le profil pour les affectations
        }

        // ===================== 5. AFFECTATIONS =====================
        // Répartition réaliste : Moroni, Mutsamudu, 1 historique, 4 non affectés

        foreach ($profils as $i => $enseignant) {
            // Enseignants 17 à 20 : NON affectés (pour tester la création !)
            if ($i >= 17) {
                continue;
            }

            // Enseignant 16 : un HISTORIQUE (ancienne fermée + actuelle ouverte)
            if ($i === 16) {
                // Ancienne affectation : École de Fomboni, terminée
                $ancienne = new Affectation();
                $ancienne->setClasse('CE2');
                $ancienne->setMatiere('Mathématiques');
                $ancienne->setDateDebut(new \DateTime('2022-09-05'));
                $ancienne->setDateFin(new \DateTime('2024-06-28'));
                $ancienne->setStatut('terminee');
                $ancienne->setEnseignant($enseignant);
                $ancienne->setEtablissement($ecoleFomboni);
                $manager->persist($ancienne);

                // Affectation actuelle : Lycée de Mutsamudu, en cours
                $actuelle = new Affectation();
                $actuelle->setClasse('Terminale');
                $actuelle->setMatiere('Français');
                $actuelle->setDateDebut(new \DateTime('2024-09-02'));
                $actuelle->setDateFin(null);
                $actuelle->setStatut('en_cours');
                $actuelle->setEnseignant($enseignant);
                $actuelle->setEtablissement($lyceeMutsamudu);
                $manager->persist($actuelle);

                continue;
            }

            // Enseignants 1-10 : Lycée de Moroni · Enseignants 11-15 : Lycée de Mutsamudu
            $etablissement = ($i <= 10) ? $lyceeMoroni : $lyceeMutsamudu;

            $affectation = new Affectation();
            $affectation->setClasse('Terminale');
            $affectation->setMatiere($i % 2 === 0 ? 'Mathématiques' : 'Français');
            $affectation->setDateDebut(new \DateTime('2024-09-02'));
            $affectation->setDateFin(null);
            $affectation->setStatut('en_cours');
            $affectation->setEnseignant($enseignant);
            $affectation->setEtablissement($etablissement);
            $manager->persist($affectation);
        }
        $manager->flush();
    }
}

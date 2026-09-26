<?php

namespace App\Repository;

use App\Entity\Affectation;
use App\Entity\Enseignant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Etablissement;

/**
 * @extends ServiceEntityRepository<Affectation>
 */
class AffectationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Affectation::class);
    }

    /**
     * Toutes les affectations ACTUELLES (non terminées) d'un établissement.
     */
    public function findActivesParEtablissement(Etablissement $etablissement): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.etablissement = :etablissement')
            ->andWhere('a.dateFin IS NULL')
            ->setParameter('etablissement', $etablissement)
            ->orderBy('a.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * L'affectation EN COURS d'un enseignant (ou null s'il n'en a pas).
     */
    public function findUneActive(Enseignant $enseignant): ?Affectation
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.enseignant = :enseignant')
            ->andWhere('a.dateFin IS NULL')
            ->setParameter('enseignant', $enseignant)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * TOUTES les affectations d'un enseignant, de la plus récente à la plus ancienne.
     */
    public function findHistorique(Enseignant $enseignant): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.enseignant = :enseignant')
            ->setParameter('enseignant', $enseignant)
            ->orderBy('a.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countEnCours(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateFin IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

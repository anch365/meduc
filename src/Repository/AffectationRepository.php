<?php

namespace App\Repository;

use App\Entity\Affectation;
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
    //    /**
    //     * @return Affectation[] Returns an array of Affectation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Affectation
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

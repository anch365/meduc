<?php

namespace App\Repository;

use App\Entity\Enseignant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enseignant>
 */
class EnseignantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enseignant::class);
    }

     /**
     * Les enseignants dont le compte est ACTIF (archivés exclus).
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.utilisateur', 'u')
            ->andWhere('u.actif = true')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Enseignant[] Returns an array of Enseignant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Enseignant
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

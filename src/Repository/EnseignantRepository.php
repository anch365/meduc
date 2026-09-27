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
            ->addSelect('u')
            ->leftJoin('e.affectations', 'a')
            ->addSelect('a')
            ->where('u.actif = true')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActifsPaginated(?string $search, string $sort, string $order, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin('e.utilisateur', 'u')
            ->addSelect('u')
            ->where('u.actif = true');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.matricule LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orderBy = match ($sort) {
            'matricule' => 'u.matricule',
            'prenom'    => 'u.prenom',
            'nom'       => 'u.nom',
            default     => 'u.nom',
        };
        $direction = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($orderBy, $direction);

        $total = (clone $qb)->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        return ['items' => $qb->getQuery()->getResult(), 'total' => (int) $total];
    }
    
    /**
     * Les enseignants dont le compte est INACTIF (archivés).
     */
    public function findArchives(): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.utilisateur', 'u')
            ->andWhere('u.actif = false')
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

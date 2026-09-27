<?php

namespace App\Repository;

use App\Entity\Etablissement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etablissement>
 */
class EtablissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etablissement::class);
    }

    public function findPaginated(?string $search, string $sort, string $order, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('et')
            ->leftJoin('et.localite', 'l')
            ->addSelect('l');

        if ($search) {
            $qb->andWhere('et.nom LIKE :search OR l.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orderBy = match ($sort) {
            'localite' => 'l.nom',
            'nom'      => 'et.nom',
            default    => 'et.nom',
        };
        $direction = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($orderBy, $direction);

        $total = (clone $qb)->select('COUNT(et.id)')->getQuery()->getSingleScalarResult();
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        return ['items' => $qb->getQuery()->getResult(), 'total' => (int) $total];
    }
    //    /**
    //     * @return Etablissement[] Returns an array of Etablissement objects
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

    //    public function findOneBySomeField($value): ?Etablissement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

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

    public function findPaginated(?string $search, string $sort, string $order, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.enseignant', 'e')
            ->innerJoin('e.utilisateur', 'u')
            ->innerJoin('a.etablissement', 'et')
            ->addSelect('e', 'u', 'et');

        // RECHERCHE : WHERE + LIKE (requête préparée via setParameter !)
        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR a.classe LIKE :search OR a.matiere LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // TRI : WHITELIST obligatoire (jamais la valeur brute de l'utilisateur dans ORDER BY !)
        $orderBy = match ($sort) {
            'etablissement' => 'et.nom',
            'classe'        => 'a.classe',
            'matiere'       => 'a.matiere',
            'debut'         => 'a.dateDebut',
            default         => 'a.dateDebut',
        };
        $direction = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy($orderBy, $direction);

        // 📄 COMPTAGE : on clone la requête et on remplace le SELECT par un COUNT
        $total = (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        // 📄 PAGINATION : LIMIT + OFFSET
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'total' => (int) $total,
        ];
    }

    public function findActiveByEnseignantIds(array $ids): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.enseignant', 'e')
            ->innerJoin('a.etablissement', 'et')
            ->addSelect('e', 'et')
            ->where('a.dateFin IS NULL')
            ->andWhere('e.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}

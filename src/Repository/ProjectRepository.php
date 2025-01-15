<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }


    /**
     * Récupère les projets filtrés par statut et triés par date de fin.
     */
    public function findByStatusAndDateFin(?string $statut_envoye): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        // Si un statut est spécifié, on filtre les projets par ce statut
        if ($statut_envoye !== null) {
            $queryBuilder->andWhere('p.statut_projet = :statut')
                         ->setParameter('statut', $statut_envoye);
        }

        // Trie les projets par date de fin (ordre croissant)
        $queryBuilder->orderBy('p.date_fin', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }


    /**
 * Récupère les projets d'un manager spécifique filtrés par statut.
 */
public function findByManagerAndStatus(int $user_id, ?string $statut_envoye): array
{
    $queryBuilder = $this->createQueryBuilder('p')
        ->andWhere('p.user = :user_id')
        ->setParameter('user_id', $user_id);

    // Si un statut est spécifié, on filtre les projets par ce statut
    if ($statut_envoye !== null) {
        $queryBuilder->andWhere('p.statut_projet = :statut')
                     ->setParameter('statut', $statut_envoye);
    }

    // Trie les projets par date de fin (ordre croissant)
    $queryBuilder->orderBy('p.date_fin', 'ASC');

    return $queryBuilder->getQuery()->getResult();
}


//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findSortieByFilters(?int $siteId, ?\DateTimeInterface $dateMin, ?\DateTimeInterface $dateMax, $organisateur, $inscrit, $nonInscrit, $sortiesPassees, ?Participant $participant, ?string $recherche)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.site', 'site')
            ->addSelect('site')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p')
            ->leftJoin('s.etat', 'etat')
            ->addSelect('etat');

        if ($siteId) {
            $qb->andWhere('site.id = :siteId')
            ->setParameter('siteId', $siteId);
        }

        if($dateMin){
            $qb->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $dateMin);
        }

        if($dateMax){
            $qb->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $dateMax);
        }

        if($organisateur && $participant){
            $qb->andWhere('s.organisateur = :participant')
                ->setParameter('participant', $participant);
        }

        if($inscrit && $participant){
            $qb->andWhere(':participant MEMBER OF s.participants')
                ->setParameter('participant', $participant);
        }

        if($nonInscrit && $participant){
            $qb->andWhere(':participant NOT MEMBER OF s.participants')
                ->setParameter('participant', $participant);
        }

         if ($recherche) {
            $qb->andWhere('s.nom LIKE :recherche')
            ->setParameter('recherche', '%' . $recherche . '%');
        }

        if($sortiesPassees){
            $qb->andWhere('etat.id IN (:etatId)')
                ->setParameter('etatId', [11, 12]);
        }

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

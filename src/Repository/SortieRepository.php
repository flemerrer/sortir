<?php

    namespace App\Repository;

    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Models\SortieSearchFilters;
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

        public function findSortieByFilters(SortieSearchFilters $filters)
        {
            $qb = $this->createQueryBuilder('s')
                ->leftJoin('s.site', 'site')
                ->addSelect('site')
                ->leftJoin('s.participants', 'p')
                ->addSelect('p')
                ->leftJoin('s.etat', 'etat')
                ->addSelect('etat');

            if ($filters->siteId) {
                $qb->andWhere('site.id = :siteId')
                    ->setParameter('siteId', $filters->siteId);
            }

            if ($filters->dateMin) {
                $qb->andWhere('s.dateHeureDebut >= :dateMin')
                    ->setParameter('dateMin', $filters->dateMin);
            }

            if ($filters->dateMax) {
                $qb->andWhere('s.dateHeureDebut <= :dateMax')
                    ->setParameter('dateMax', $filters->dateMax);
            }

            if ($filters->organisateur && $filters->participant) {
                $qb->andWhere('s.organisateur = :participant')
                    ->setParameter('participant', $filters->participant);
            }

            if ($filters->inscrit && $filters->participant) {
                $qb->andWhere(':participant MEMBER OF s.participants')
                    ->setParameter('participant', $filters->participant);
            }

            if ($filters->nonInscrit && $filters->participant) {
                $qb->andWhere(':participant NOT MEMBER OF s.participants')
                    ->setParameter('participant', $filters->participant);
            }

            if ($filters->recherche) {
                $qb->andWhere('s.nom LIKE :recherche')
                    ->setParameter('recherche', '%' . $filters->recherche . '%');
            }

            if ($filters->sortiesPassees) {
                $qb->andWhere('etat.libelle IN (:etatLibelles)')
                    ->setParameter('etatLibelles', ["Passée", "Annulée"]);
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

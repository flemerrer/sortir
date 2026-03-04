<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Models\EtatLibelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Etat>
 */
class EtatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etat::class);
    }

    public function findCreee(): ?Etat
    {
        return $this->findOneBy(['libelle' => EtatLibelle::CREEE->value]);
    }

    public function findOuverte(): ?Etat
    {
        return $this->findOneBy(['libelle' => EtatLibelle::OUVERTE->value]);
    }

    public function findCloturee()
    {
        return $this->findOneBy(["libelle" => EtatLibelle::CLOTUREE->value]);
    }

    public function findEnCours()
    {
        return $this->findOneBy(["libelle" => EtatLibelle::EN_COURS->value]);
    }

    public function findPassee()
    {
        return $this->findOneBy(["libelle" => EtatLibelle::PASSEE->value]);
    }

    public function findAnnulee()
    {
        return $this->findOneBy(["libelle" => EtatLibelle::ANNULEE->value]);
    }

    public function findArchivee()
    {
        return $this->findOneBy(["libelle" => EtatLibelle::ARCHIVEE->value]);
    }

    //    /**
    //     * @return Etat[] Returns an array of Etat objects
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

    //    public function findOneBySomeField($value): ?Etat
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

}

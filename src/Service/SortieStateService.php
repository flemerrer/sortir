<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Repository\EtatRepository;
    use App\Repository\SortieRepository;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\ORM\EntityManagerInterface;

    /**
     * Service de la couche métier qui gère les inscriptions aux sorties
     */
    class SortieStateService
    {
        public function __construct(
            private EntityManagerInterface $em,
            private SortieRepository       $sortieRepository,
            private EtatRepository         $etatRepository
        )
        {
        }

        public function archiveSorties()
        {

            $now = new \DateTime();
            $archivee = $this->etatRepository->findOneBy(["libelle" => 'Archivée']);
            $passee = $this->etatRepository->findOneBy(["libelle" => 'Passée']);

            $archivalDate = $now->sub(new \DateInterval('P30D'))->setTime(0, 0, 0);

            $queryBuilder = $this->sortieRepository->createQueryBuilder('s');
            $queryBuilder->update()
                ->set('s.etat', ':etat_archivee')
                ->where('s.etat = :etat_passee')
                ->andWhere('s.dateHeureDebut < :archival_date')
                ->setParameter('etat_archivee', $archivee)
                ->setParameter('etat_passee', $passee)
                ->setParameter('archival_date', $archivalDate)
                ->getQuery()
                ->execute();

            $this->em->flush();
        }

        public function startSorties()
        {
            $now = new \DateTime();
            $cloturee = $this->etatRepository->findOneBy(["libelle" => 'Clôturée']);
            $ongoing = $this->etatRepository->findOneBy(["libelle" => 'Activité en cours']);
            $queryBuilder = $this->sortieRepository->createQueryBuilder('s');
            $queryBuilder->update()
                ->set('s.etat', ':etat_en_cours')
                ->where('s.etat = :etat_cloturee')
                ->andWhere('s.dateHeureDebut < :now')
                ->setParameter('etat_en_cours', $ongoing)
                ->setParameter('etat_cloturee', $cloturee)
                ->setParameter('now', $now)
                ->getQuery()
                ->execute();

            $this->em->flush();
        }

        public function endSorties()
        {
            $now = new \DateTime();
            $ongoing = $this->etatRepository->findOneBy(["libelle" => 'Activité en cours']);
            $passee = $this->etatRepository->findOneBy(["libelle" => 'Passée']);

            $ongoingSorties = $this->sortieRepository->findBy(['etat' => $ongoing]);
            foreach ($ongoingSorties as $sortie) {
                $interval = new \DateInterval("PT{$sortie->getDuree()}M");
                if ($sortie->getDateHeureDebut()->add($interval) <= $now) {
                    $sortie->setEtat($passee);
                }
            }

            $this->em->flush();
        }

    }

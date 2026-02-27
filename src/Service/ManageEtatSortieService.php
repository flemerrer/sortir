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
    class ManageEtatSortieService
    {
        public function __construct(
            private EntityManagerInterface $em,
            private SortieRepository       $sortieRepository,
            private EtatRepository         $etatRepository
        )
        {
        }

        public function finishSorties()
        {
            $now = new \DateTime();
            $etatMap = $this->etatRepository->getEtats();

            $queryBuilder = $this->sortieRepository->createQueryBuilder('s');
            $queryBuilder->update()
                ->set('s.etat', ':etat_passee')
                ->where('s.etat = :etat_cloturee')
                ->andWhere('s.dateHeureDebut < :today')
                ->setParameters(new ArrayCollection([
                    'etat_passee' => $etatMap['Passée'],
                    'etat_cloturee' => $etatMap['Clôturée'],
                    'today' => $now->format('Y-m-d 00:00:00'),
                ]))
                ->getQuery()
                ->execute();
            $this->em->flush();
        }

        public function archiveSorties()
        {

            $now = new \DateTime();
            $etatMap = $this->etatRepository->getEtats();

            $archivalDate = $now->sub(new \DateInterval('P30D'))->setTime(0, 0, 0);

            $queryBuilder = $this->sortieRepository->createQueryBuilder('s');
            $queryBuilder->update()
                ->set('s.etat', ':etat_archivee')
                ->where('s.etat = :etat_passee')
                ->andWhere('s.dateHeureDebut < :archival_date')
                ->setParameters(new ArrayCollection([
                    'etat_archivee' => $etatMap['Archivée'],
                    'etat_passee' => $etatMap['Passée'],
                    'archival_date' => $archivalDate->format('Y-m-d 00:00:00'),
                ]))
                ->getQuery()
                ->execute();

            $this->em->flush();
        }

        public function startSorties()
        {
            $now = new \DateTime();
            $etatMap = $this->etatRepository->getEtats();

            $onStandbySorties = $this->sortieRepository->findBy(['etat' => $etatMap['Clôturée']]);
            foreach ($onStandbySorties as $sortie) {
                if ($sortie->getDateHeureDebut() <= $now) {
                    $sortie->setEtat($etatMap['Activité en cours']);
                }
            }
        }

        public function endSorties()
        {
            $now = new \DateTime();
            $etatMap = $this->etatRepository->getEtats();

            $ongoingSorties = $this->sortieRepository->findBy(['etat' => $etatMap['En cours']]);
            foreach ($ongoingSorties as $sortie) {
                $interval = new \DateInterval("{$sortie->getDuree()}M");
                if ($sortie->getDateHeureDebut()->add($interval) <= $now) {
                    $sortie->setEtat($etatMap['Passée']);
                }
            }
        }

    }

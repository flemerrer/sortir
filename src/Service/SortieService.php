<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Exception\SortieCancelException;
    use App\Exception\SortieCreateException;
    use App\Exception\SortieDeleteException;
    use App\Exception\SortieFetchFilteredException;
    use App\Exception\SortiePublishException;
    use App\Exception\LieuCreateException;
    use App\Models\EtatLibelle;
    use App\Models\SortieDTO;
    use App\Models\SortieSearchFilters;
    use App\Repository\EtatRepository;
    use App\Repository\SortieRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\InputBag;

    /**
     * Service responsable de la gestion des Sorties
     */
    class SortieService
    {
        public function __construct(
            private readonly EntityManagerInterface $em,
            private readonly LieuService            $lieuService,
            private readonly EtatRepository         $etatRepository,
            private readonly SortieRepository       $sortieRepository,
            private readonly LoggerInterface        $logger
        )
        {
        }

        /**
         * @param SortieDTO $dto
         * @throws SortieCreateException
         */
        public function createSortieFromDTO(SortieDTO $dto, Participant $user): Sortie
        {
            try {
                $sortie = new Sortie();
                $sortie->buildFromDTO($dto);
                $this->addOrCreateLieu($dto, $sortie);
                $sortie->setOrganisateur($user);
                $sortie->addParticipant($user);
                $sortie->setSite($user->getSite());
                $etat = $this->etatRepository->findCreee();
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
                $this->em->flush();
                return $sortie;
//                We should be able to narrow specific exceptions and add custom messages for each case
//                Add new catch clauses when errors are encoutered
            } catch (\Exception $e) {
                $this->logger->error('Error creating sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new SortieCreateException();
            }
        }

        /**
         * @param SortieDTO $dto
         * @param Sortie $sortie
         * @return void
         * @throws LieuCreateException
         */
        public function updateSortie(SortieDTO $dto, Sortie $sortie): void
        {
            try {
                $sortie->buildFromDTO($dto);
                $this->addOrCreateLieu($dto, $sortie);
                $sortie->setSite($dto->site);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error updating sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new LieuCreateException();
            }
        }

        /**
         * @param SortieDTO $sortieDTO
         * @param Sortie $sortie
         * @return void
         */
        public function addOrCreateLieu(SortieDTO $sortieDTO, Sortie $sortie): void
        {
            if ($sortieDTO->nomNouveauLieu && $sortieDTO->rueNouveauLieu) {
                $lieu = $this->lieuService->createLieuFromDTO($sortieDTO);
                $sortie->setLieu($lieu);
            } else {
                $sortie->setLieu($sortieDTO->lieuxDisponibles);
            }
        }

        /**
         * @param Sortie $sortie
         * @return void
         * @throws SortiePublishException
         */
        public function publishSortie(Sortie $sortie): void
        {
            try {
                $open = $this->etatRepository->findOuverte();
                $sortie->setEtat($open);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error publishing sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new SortiePublishException();
            }
        }

        /**
         * @param Sortie $sortie
         * @return void
         * @throws SortieCancelException
         */
        public function cancelSortie(Sortie $sortie): void
        {
            try {
                $open = $this->etatRepository->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($open);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error canceling sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new SortieCancelException();
            }
        }

        /**
         * @param Sortie $sortie
         * @return void
         * @throws SortieDeleteException
         */
        public function deleteSortie(Sortie $sortie): void
        {
            try {
                $this->em->remove($sortie);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error deleting sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new SortieDeleteException();
            }
        }

        public function getSortieWithFilters(InputBag $query, Participant $user): array
        {
            try {
                $siteId = $query->getInt('site');
                $dateMin = $query->get('dateMin');
                $dateMax = $query->get('dateMax');
                $organisateur = $query->get('organisateur');
                $inscrit = $query->get('inscrit');
                $nonInscrit = $query->get('nonInscrit');
                $sortiesPassees = $query->get('sortiesPassees');
                $recherche = $query->get('recherche');
                $filters = new SortieSearchFilters();
                $filters->participant = $user;
                if ($siteId) $filters->siteId = $siteId;
                if ($dateMin) $filters->dateMin = new \DateTime($dateMin);
                if ($dateMax) $filters->dateMax = new \DateTime($dateMax);
                if ($organisateur) $filters->organisateur = true;
                if ($inscrit) $filters->inscrit = true;
                if ($nonInscrit) $filters->nonInscrit = true;
                if ($sortiesPassees) $filters->sortiesPassees = true;
                if ($recherche) $filters->recherche = $recherche;
                return $this->sortieRepository->findSortieByFilters($filters);
            } catch (\Exception $e) {
                $this->logger->error('Error filtering sorties: ' . $e->getMessage(), ['exception' => $e]);
                throw new SortieFetchFilteredException();
            }
        }
    }

<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Exception\SortieCancelException;
    use App\Exception\SortieCreateException;
    use App\Exception\SortieDeleteException;
    use App\Exception\SortieFetchFilteredException;
    use App\Exception\SortiePublishException;
    use App\Exception\SortieUpdateException;
    use App\Models\SortieDTO;
    use App\Models\SortieSearchFilters;
    use App\Repository\EtatRepository;
    use App\Repository\SortieRepository;
    use Doctrine\ORM\EntityManagerInterface;
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
            private readonly SortieRepository       $sortieRepository
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
                $etat = $this->etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
                $this->em->persist($sortie);
                $this->em->flush();
                return $sortie;
//                We should be able to narrow specific exceptions and add custom messages for each case
            } catch (\Exception $e) {
                //todo: implement logger and log the original exception
                throw new SortieCreateException($e->getMessage());
            }
        }

        /**
         * @param SortieDTO $dto
         * @param Sortie $sortie
         * @return void
         */
        public function updateSortie(SortieDTO $dto, Sortie $sortie): void
        {
            try {
                $sortie->buildFromDTO($dto);
                $this->addOrCreateLieu($dto, $sortie);
                $sortie->setSite($dto->site);
                $this->em->flush();
            } catch (\Exception $e) {
                throw new SortieUpdateException($e->getMessage());
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
                $open = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($open);
                $this->em->flush();
            } catch (\Exception $e) {
                throw new SortiePublishException($e->getMessage());
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
                throw new SortieCancelException($e->getMessage());
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
                throw new SortieDeleteException($e->getMessage());
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
                if($siteId) $filters->siteId = $siteId;
                if($dateMin) $filters->dateMin = new \DateTime($dateMin);
                if($dateMax) $filters->dateMax = new \DateTime($dateMax);
                if($organisateur) $filters->organisateur = true;
                if($inscrit) $filters->inscrit = true;
                if($nonInscrit) $filters->nonInscrit = true;
                if($sortiesPassees) $filters->sortiesPassees = true;
                if($recherche) $filters->recherche = $recherche;
                return $this->sortieRepository->findSortieByFilters($filters);
            } catch (\Exception $e) {
                throw new SortieFetchFilteredException($e->getMessage());
            }
        }
    }

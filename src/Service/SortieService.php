<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Entity\Sortie;
    use App\Exception\SortieCancelException;
    use App\Exception\SortieCreateException;
    use App\Exception\SortieDeleteException;
    use App\Exception\SortiePublishException;
    use App\Exception\SortieUpdateException;
    use App\Models\SortieDTO;
    use App\Repository\EtatRepository;
    use Doctrine\ORM\EntityManagerInterface;

    /**
     * Service responsable de la gestion des Sorties
     */
    class SortieService
    {
        public function __construct(
            private readonly EntityManagerInterface $em,
            private readonly lieuService            $etatService,
            private readonly EtatRepository         $etatRepository
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
                $lieu = $this->etatService->createLieuFromDTO($sortieDTO);
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
    }

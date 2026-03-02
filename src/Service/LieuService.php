<?php

    namespace App\Service;

    use App\Entity\Lieu;
    use App\Exception\LieuCreateException;
    use App\Models\SortieDTO;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;

    /**
     * Service responsable de la gestion des Lieux
     */
    class LieuService
    {

        public function __construct(
            private readonly EntityManagerInterface $em,
            private readonly LoggerInterface        $logger
        )
        {
        }

        /**
         * @param SortieDTO $sortieDTO
         * @return Lieu
         * @throws LieuCreateException
         */
        public function createLieuFromDTO(SortieDTO $sortieDTO)
        {
            try {
                $lieu = new Lieu();
                $lieu->setNom($sortieDTO->nomNouveauLieu);
                $lieu->setRue($sortieDTO->rueNouveauLieu);
                $lieu->setLatitude($sortieDTO->nouveauLieuLatitude);
                $lieu->setLongitude($sortieDTO->nouveauLieuLongitude);
                $lieu->setVille($sortieDTO->villesDisponibles);
                $this->em->persist($lieu);
                return $lieu;
            } catch (\Exception $e) {
                $this->logger->error('Error creating lieu: ' . $e->getMessage(), ['exception' => $e]);
                throw new LieuCreateException();
            }
        }
    }

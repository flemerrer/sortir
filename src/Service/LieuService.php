<?php

    namespace App\Service;

    use App\Entity\Lieu;
    use App\Models\SortieDTO;
    use Doctrine\ORM\EntityManagerInterface;

    /**
     * Service responsable de la gestion des Etats
     */
    class lieuService
    {


        /**
         * @param EntityManagerInterface $em
         */
        public function __construct(
            private readonly EntityManagerInterface $em
        )
        {
        }

        /**
         * @param SortieDTO $sortieDTO
         * @return Lieu
         */
        public function createLieuFromDTO(SortieDTO $sortieDTO)
        {
            $lieu = new Lieu();
            $lieu->setNom($sortieDTO->nomNouveauLieu);
            $lieu->setRue($sortieDTO->rueNouveauLieu);
            $lieu->setLatitude($sortieDTO->nouveauLieuLatitude);
            $lieu->setLongitude($sortieDTO->nouveauLieuLongitude);
            $lieu->setVille($sortieDTO->villesDisponibles);
            $this->em->persist($lieu);
            return $lieu;
        }
    }
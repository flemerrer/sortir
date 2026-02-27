<?php

namespace App\Service;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Models\SortieDTO;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service responsable de la gestion des Sorties
 */
class SortieManagerService
{

    /**
     * @param SortieDTO $sortieDTO
     * @param Sortie $sortie
     * @param EntityManagerInterface $em
     * @return void
     */
    public function addOrCreateLieu(mixed $sortieDTO, Sortie $sortie, EntityManagerInterface $em): void
    {
        if ($sortieDTO->nomNouveauLieu && $sortieDTO->rueNouveauLieu) {
            $lieu = new Lieu();
            $lieu->setNom($sortieDTO->nomNouveauLieu);
            $lieu->setRue($sortieDTO->rueNouveauLieu);
            $lieu->setLatitude($sortieDTO->nouveauLieuLatitude);
            $lieu->setLongitude($sortieDTO->nouveauLieuLongitude);
            $lieu->setVille($sortieDTO->villesDisponibles);
            $em->persist($lieu);
            $sortie->setLieu($lieu);
        } else {
            $sortie->setLieu($sortieDTO->lieuxDisponibles);
        }
    }

    /**
     * @param SortieDTO $dto
     * @param Sortie $sortie
     * @return void
     */
    public function updateSortie(SortieDTO $dto, Sortie $sortie): void
    {
        $sortie->setNom($dto->nom);
        $sortie->setDuree($dto->duree);
        $sortie->setDateHeureDebut($dto->dateHeureDebut);
        $sortie->setDateLimiteInscription($dto->dateLimiteInscription);
        $sortie->setNbInscriptionsMax($dto->nbInscriptionsMax);
        $sortie->setInfosSortie($dto->infosSortie);
        $sortie->setLieu($dto->lieu);
        $sortie->setSite($dto->site);
    }

    /**
     * @param mixed $sortieDTO
     * @return Sortie
     */
    public function createSortie(mixed $sortieDTO): Sortie
    {
        $sortie = new Sortie();
        $sortie->setNom($sortieDTO->nom);
        $sortie->setDateHeureDebut($sortieDTO->dateHeureDebut);
        $sortie->setDuree($sortieDTO->duree);
        $sortie->setDateLimiteInscription($sortieDTO->dateLimiteInscription);
        $sortie->setNbInscriptionsMax($sortieDTO->nbInscriptionsMax);
        $sortie->setInfosSortie($sortieDTO->infosSortie);
        return $sortie;
    }


}

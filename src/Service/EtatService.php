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
    class EtatService
    {
        public function getEtats(
            EtatRepository $etatRepository
        )
        {
            $etatRepository->findAll();
        }
    }

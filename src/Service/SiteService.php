<?php

    namespace App\Service;

    use App\Entity\Lieu;
    use App\Exception\SiteFetchException;
    use App\Models\SortieDTO;
    use App\Repository\SiteRepository;
    use Doctrine\ORM\EntityManagerInterface;

    /**
     * Service responsable de la gestion des Sites
     */
    class SiteService
    {

        public function __construct(
            private readonly SiteRepository $siteRepository,
        )
        {
        }

        /**
         * @return array
         * @throws SiteFetchException
         */
        public function getAllSites(): array
        {
            try {
                return $this->siteRepository->findAll();
            } catch (\Exception $e) {
                throw new SiteFetchException('Erreur lors de la récupération des sites: ' . $e->getMessage());
            }
        }

    }
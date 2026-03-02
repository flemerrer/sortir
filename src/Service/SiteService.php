<?php

    namespace App\Service;

    use App\Entity\Lieu;
    use App\Exception\SiteFetchException;
    use App\Models\SortieDTO;
    use App\Repository\SiteRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;

    /**
     * Service responsable de la gestion des Sites
     */
    class SiteService
    {

        public function __construct(
            private readonly SiteRepository $siteRepository,
            private readonly LoggerInterface $logger
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
                $this->logger->error('Error creating sortie: ' . $e->getMessage(), ['exception' => $e]);
                throw new SiteFetchException();
            }
        }

    }
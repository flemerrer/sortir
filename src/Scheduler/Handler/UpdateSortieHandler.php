<?php

    namespace App\Scheduler\Handler;

    use App\Repository\EtatRepository;
    use App\Scheduler\Message\UpdateSortie;
    use App\Service\SortieStateService;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;

    #[AsMessageHandler]
    class UpdateSortieHandler
    {

        public function __construct(
            private readonly LoggerInterface $logger,
            private readonly SortieStateService $sortieEtatService
        )
        {
        }

        public function __invoke(
            UpdateSortie $message
        )
        {
            $this->logger->debug("Running UpdateSortie scheduled job");
            try {
                $this->sortieEtatService->startSorties();
                $this->sortieEtatService->endSorties();
            } catch (\Exception $e) {
                $this->logger->error("Failed to execute UpdateSortie scheduled job : {$e}");
            }
        }

    }

<?php

    namespace App\Scheduler\Handler;

    use App\Scheduler\Message\UpdateSortie;
    use App\Service\SortieStateService;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\Messenger\Attribute\AsMessageHandler;

    #[AsMessageHandler]
    class ArchiveSortieHandler
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
            $this->logger->debug("Running ArchiveSortie scheduled job");
            try {
                $this->sortieEtatService->archiveSorties();
            } catch (\Exception $e) {
                $this->logger->error("Failed to execute ArchiveSortie scheduled job : {$e}");
            }
        }

    }

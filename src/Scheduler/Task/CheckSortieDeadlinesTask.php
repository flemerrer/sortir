<?php

    namespace App\Scheduler\Task;

    use App\Service\ManageEtatSortieService;

    class CheckSortieDeadlinesTask
    {
        public function __construct(
            private readonly ManageEtatSortieService $sortieEtatService
        )
        {
        }

        public function __invoke(): void
        {
            try {
                $this->sortieEtatService->finishSorties();
                $this->sortieEtatService->archiveSorties();
            } catch (\Exception $e) {
            }
        }
    }

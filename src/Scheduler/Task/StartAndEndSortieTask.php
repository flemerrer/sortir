<?php

    namespace App\Scheduler\Task;

    use App\Service\ManageEtatSortieService;

    class StartAndEndSortieTask
    {

        public function __construct(
            private readonly ManageEtatSortieService $sortieEtatService
        )
        {
        }

        public function __invoke(): void
        {
            try {
                $this->sortieEtatService->startSorties();
                $this->sortieEtatService->endSorties();
            } catch (\Exception $e) {
            }
        }

    }
<?php

    namespace App\Models;

    use App\Entity\Participant;

    class SortieSearchFilters
    {
        public ?Participant $participant = null;
        public ?int $siteId = null;
        public ?\DateTimeInterface $dateMin = null;
        public ?\DateTimeInterface $dateMax = null;
        public ?bool $organisateur = false;
        public ?bool $inscrit = false;
        public ?bool $nonInscrit = false;
        public ?bool $sortiesPassees = false;
        public ?string $recherche = null;

    }
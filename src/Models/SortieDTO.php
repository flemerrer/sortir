<?php

    namespace App\Models;

    use App\Entity\Lieu;
    use App\Entity\Site;
    use App\Entity\Sortie;
    use App\Entity\Ville;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Validator\Constraints as Assert;

    class SortieDTO
    {
        public ?int $id = null;
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 90)]
        public ?string $nom = null;
        public ?Site $site = null;
        #[Assert\GreaterThan(14)]
        public ?int $duree = null;
        public ?\DateTimeImmutable $dateHeureDebut = null;
        public ?\DateTimeImmutable $dateLimiteInscription = null;
        #[Assert\GreaterThan(0)]
        public ?int $nbInscriptionsMax = null;
        public ?string $infosSortie = null;
        public ?Lieu $lieuxDisponibles = null;
        public ?Ville $villesDisponibles = null;
        public ?Lieu $lieu = null;
        #[Assert\Length(min: 2, max: 90)]
        public ?string $nomNouveauLieu = null;
        #[Assert\Length(min: 2, max: 90)]
        public ?string $rueNouveauLieu = null;
        public ?string $nouveauLieuLatitude = null;
        public ?string $nouveauLieuLongitude = null;

// TODO: ajouter la possibilité de créer une nouvelle ville en même temps que la sortie
//            public ?string $nomNouvelleVille,
//            public ?string $codePostalNouvelleVille

        public function loadSortie(Sortie $sortie): void
        {
            $this->id = $sortie->getId();
            $this->nom = $sortie->getNom();
            $this->duree = $sortie->getDuree();
            $this->site = $sortie->getSite();
            $this->dateHeureDebut = $sortie->getDateHeureDebut();
            $this->dateLimiteInscription = $sortie->getDateLimiteInscription();
            $this->nbInscriptionsMax = $sortie->getNbInscriptionsMax();
            $this->infosSortie = $sortie->getInfosSortie();
            $this->lieuxDisponibles = $sortie->getLieu();
        }

        public function toSortie(): Sortie
        {
            $sortie = new Sortie();
            $sortie->setNom($this->nom);
            $sortie->setDateHeureDebut($this->dateHeureDebut);
            $sortie->setDuree($this->duree);
            $sortie->setDateLimiteInscription($this->dateLimiteInscription);
            $sortie->setNbInscriptionsMax($this->nbInscriptionsMax);
            $sortie->setInfosSortie($this->infosSortie);
            return $sortie;
        }
    }

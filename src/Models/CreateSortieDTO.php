<?php

    namespace App\Models;

    use App\Entity\Lieu;
    use App\Entity\Sortie;
    use App\Entity\Ville;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Validator\Constraints as Assert;

    class CreateSortieDTO
    {
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 90)]
        public ?string $nom = null;
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

// TODO: ajouter la possibilité de créer une nouvelle ville en même temps que la sortie
//            public ?string $nomNouvelleVille,
//            public ?string $codePostalNouvelleVille

        public function toSortie(EntityManagerInterface $em)
        {
            $sortie = new Sortie();
            $sortie->setNom($this->nom);
            $sortie->setDuree($this->duree);
            $sortie->setDateHeureDebut($this->dateHeureDebut);
            $sortie->setDateLimiteInscription($this->dateLimiteInscription);
            $sortie->setNbInscriptionsMax($this->nbInscriptionsMax);
            $sortie->setInfosSortie($this->infosSortie);
            $sortie->setLieu($this->lieu);
            return $sortie;
        }
    }

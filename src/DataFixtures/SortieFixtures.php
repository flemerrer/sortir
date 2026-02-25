<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $sortie1 = new Sortie();
        $sortie1->setNom("Afterwork nems et ti ponche");
        $sortie1->setDateHeureDebut(new \DateTimeImmutable("2026-09-31 18:30:00"));
        $sortie1->setDateLimiteInscription(new \DateTimeImmutable("2026-09-25"));
        $sortie1->setDuree(240);
        $sortie1->setNbInscriptionsMax(15);
        $sortie1->setEtat($this->getReference("Créée", Etat::class));
        $participant1 = $this->getReference("participant1", Participant::class);
        $sortie1->setOrganisateur($participant1);
        $sortie1->addParticipant($participant1);
        $sortie1->setLieu($this->getReference("AVEC", Lieu::class));
        $manager->persist($sortie1);

        $sortie2 = new Sortie();
        $sortie2->setNom("Babyfoot et bières sans alcool");
        $sortie2->setDateHeureDebut(new \DateTimeImmutable("2026-09-15 18:30:00"));
        $sortie2->setDateLimiteInscription(new \DateTimeImmutable("2026-09-12"));
        $sortie2->setDuree(240);
        $sortie2->setNbInscriptionsMax(6);
        $sortie2->setEtat($this->getReference("Ouverte", Etat::class));
        $participant2 = $this->getReference("participant2", Participant::class);
        $sortie2->setOrganisateur($participant2);
        $sortie2->addParticipant($participant2);
        $sortie2->setLieu($this->getReference("Le Labo", Lieu::class));
        $manager->persist($sortie2);

        $sortie3 = new Sortie();
        $sortie3->setNom("Boîte de jour et bubble tea");
        $sortie3->setDateHeureDebut(new \DateTimeImmutable("2026-09-12 18:30:00"));
        $sortie3->setDateLimiteInscription(new \DateTimeImmutable("2026-09-07"));
        $sortie3->setDuree(240);
        $sortie3->setNbInscriptionsMax(15);
        $sortie3->setEtat($this->getReference("Annulée", Etat::class));
        $participant3 = $this->getReference("participant3", Participant::class);
        $sortie3->setOrganisateur($participant3);
        $sortie3->addParticipant($participant3);
        $sortie3->setLieu($this->getReference("The Narrow Lounge", Lieu::class));
        $manager->persist($sortie3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ParticipantFixtures::class, EtatFixtures::class, LieuFixtures::class];
    }
}

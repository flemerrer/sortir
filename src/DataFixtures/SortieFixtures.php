<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $sortie = new Sortie();
        $sortie->setNom("Afterwork nems et ti ponche");
        $sortie->setDateHeureDebut(new \DateTimeImmutable("2026-09-31 18:30:00"));
        $sortie->setDateLimiteInscription(new \DateTimeImmutable("2026-09-25"));
        $sortie->setDuree(240);
        $sortie->setNbInscriptionsMax(15);
        $sortie->setEtat($this->getReference("Créée", Etat::class));
        $participant1 = $this->getReference("participant1", Participant::class);
        $sortie->setOrganisateur($participant1);
        $sortie->addParticipant($participant1);
        $manager->persist($sortie);

        $sortie = new Sortie();
        $sortie->setNom("Babyfoot et bières sans alcool");
        $sortie->setDateHeureDebut(new \DateTimeImmutable("2026-09-15 18:30:00"));
        $sortie->setDateLimiteInscription(new \DateTimeImmutable("2026-09-12"));
        $sortie->setDuree(240);
        $sortie->setNbInscriptionsMax(6);
        $sortie->setEtat($this->getReference("Ouverte", Etat::class));
        $participant1 = $this->getReference("participant2", Participant::class);
        $sortie->setOrganisateur($participant1);
        $sortie->addParticipant($participant1);
        $manager->persist($sortie);

        $sortie = new Sortie();
        $sortie->setNom("Boîte de jour et bubble tea");
        $sortie->setDateHeureDebut(new \DateTimeImmutable("2026-09-12 18:30:00"));
        $sortie->setDateLimiteInscription(new \DateTimeImmutable("2026-09-07"));
        $sortie->setDuree(240);
        $sortie->setNbInscriptionsMax(15);
        $sortie->setEtat($this->getReference("Annulée", Etat::class));
        $participant1 = $this->getReference("participant3", Participant::class);
        $sortie->setOrganisateur($participant1);
        $sortie->addParticipant($participant1);
        $manager->persist($sortie);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ParticipantFixtures::class, EtatFixtures::class];
    }
}

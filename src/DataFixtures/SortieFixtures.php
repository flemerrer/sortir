<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Models\EtatLibelle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $sortie0 = new Sortie();
        $sortie0->setNom("Slip n slide");
        $sortie0->setDateHeureDebut(new \DateTimeImmutable("2025-09-31 18:30:00"));
        $sortie0->setDateLimiteInscription(new \DateTimeImmutable("2025-09-25"));
        $sortie0->setDuree(175);
        $sortie0->setNbInscriptionsMax(10);
        $sortie0->setEtat($this->getReference(EtatLibelle::PASSEE->value, Etat::class));
        $participant1 = $this->getReference("participant1", Participant::class);
        $sortie0->setOrganisateur($participant1);
        $sortie0->addParticipant($participant1);
        $sortie0->setLieu($this->getReference("AVEC", Lieu::class));
        $sortie0->setSite($this->getReference("Rennes", Site::class));
        $manager->persist($sortie0);

        $sortie1 = new Sortie();
        $sortie1->setNom("Afterwork nems et ti ponche");
        $sortie1->setDateHeureDebut(new \DateTimeImmutable("2026-03-02 15:30:00"));
        $sortie1->setDateLimiteInscription(new \DateTimeImmutable("2026-03-2"));
        $sortie1->setDuree(240);
        $sortie1->setNbInscriptionsMax(15);
        $sortie1->setEtat($this->getReference(EtatLibelle::CLOTUREE->value, Etat::class));
        $participant1 = $this->getReference("participant1", Participant::class);
        $sortie1->setOrganisateur($participant1);
        $sortie1->addParticipant($participant1);
        $sortie1->setLieu($this->getReference("AVEC", Lieu::class));
        $sortie1->setSite($this->getReference("Rennes", Site::class));
        $manager->persist($sortie1);

        $sortie2 = new Sortie();
        $sortie2->setNom("Babyfoot et bières sans alcool");
        $sortie2->setDateHeureDebut(new \DateTimeImmutable("2026-03-02 11:30:00"));
        $sortie2->setDateLimiteInscription(new \DateTimeImmutable("2026-03-02"));
        $sortie2->setDuree(240);
        $sortie2->setNbInscriptionsMax(6);
        $sortie2->setEtat($this->getReference(EtatLibelle::EN_COURS->value, Etat::class));
        $participant2 = $this->getReference("participant2", Participant::class);
        $sortie2->setOrganisateur($participant2);
        $sortie2->addParticipant($participant2);
        $sortie2->setLieu($this->getReference("Le Labo", Lieu::class));
        $sortie1->setSite($this->getReference("Nantes", Site::class));
        $manager->persist($sortie2);

        $sortie3 = new Sortie();
        $sortie3->setNom("Boîte de jour et bubble tea");
        $sortie3->setDateHeureDebut(new \DateTimeImmutable("2026-09-12 18:30:00"));
        $sortie3->setDateLimiteInscription(new \DateTimeImmutable("2026-09-07"));
        $sortie3->setDuree(240);
        $sortie3->setNbInscriptionsMax(15);
        $sortie3->setEtat($this->getReference(EtatLibelle::OUVERTE->value, Etat::class));
        $participant3 = $this->getReference("participant3", Participant::class);
        $sortie3->setOrganisateur($participant3);
        $sortie3->addParticipant($participant3);
        $sortie3->setLieu($this->getReference("The Narrow Lounge", Lieu::class));
        $sortie1->setSite($this->getReference("Vancouver", Site::class));
        $manager->persist($sortie3);

        $sortie4 = new Sortie();
        $sortie4->setNom("Boîte de nuit carpette");
        $sortie4->setDateHeureDebut(new \DateTimeImmutable("2026-09-05 18:30:00"));
        $sortie4->setDateLimiteInscription(new \DateTimeImmutable("2026-09-01"));
        $sortie4->setDuree(240);
        $sortie4->setNbInscriptionsMax(15);
        $sortie4->setEtat($this->getReference(EtatLibelle::ANNULEE->value, Etat::class));
        $participant3 = $this->getReference("participant3", Participant::class);
        $sortie4->setOrganisateur($participant3);
        $sortie4->addParticipant($participant3);
        $sortie4->setLieu($this->getReference("The Narrow Lounge", Lieu::class));
        $sortie1->setSite($this->getReference("Vancouver", Site::class));
        $manager->persist($sortie4);

        $sortie5 = new Sortie();
        $sortie5->setNom("Beach Volley et mojitos sans alcool");
        $sortie5->setDateHeureDebut(new \DateTimeImmutable("2027-06-15 13:30:00"));
        $sortie5->setDateLimiteInscription(new \DateTimeImmutable("2027-06-12"));
        $sortie5->setDuree(240);
        $sortie5->setNbInscriptionsMax(15);
        $sortie5->setEtat($this->getReference(EtatLibelle::CREEE->value, Etat::class));
        $participant3 = $this->getReference("participant3", Participant::class);
        $sortie5->setOrganisateur($participant3);
        $sortie5->addParticipant($participant3);
        $sortie5->setLieu($this->getReference("Plage des libraires", Lieu::class));
        $sortie1->setSite($this->getReference("Nantes", Site::class));
        $manager->persist($sortie5);

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [ParticipantFixtures::class, EtatFixtures::class, LieuFixtures::class];
    }
}

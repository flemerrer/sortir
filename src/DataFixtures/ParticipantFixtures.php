<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $participant1 = new Participant();
        $participant1->setNom("LE MERRER");
        $participant1->setPrenom("François");
        $participant1->setPseudo("flemerrer");
        $participant1->setTelephone("0123456789");
        $participant1->setEmail("flemerrer@email.tld");
        $participant1->setMotDePasse("password123");
        $participant1->setAdministrateur(true);
        $participant1->setActif(true);
        $participant1->setSite($this->getReference("Rennes", Site::class));
        $this->addReference("participant1", $participant1);
        $manager->persist($participant1);

        $participant2 = new Participant();
        $participant2->setNom("HERAUD");
        $participant2->setPrenom("Alicia");
        $participant2->setPseudo("aheraud");
        $participant2->setTelephone("0123456789");
        $participant2->setEmail("aheraud@email.tld");
        $participant2->setMotDePasse("password123");
        $participant2->setAdministrateur(true);
        $participant2->setActif(true);
        $participant1->setSite($this->getReference("Nantes", Site::class));
        $this->addReference("participant2", $participant2);
        $manager->persist($participant2);

        $participant3 = new Participant();
        $participant3->setNom("DENIZANE");
        $participant3->setPrenom("Maxence");
        $participant3->setPseudo("mdenizane");
        $participant3->setTelephone("0123456789");
        $participant3->setEmail("mdenizane@email.tld");
        $participant3->setMotDePasse("password123");
        $participant3->setAdministrateur(true);
        $participant3->setActif(true);
        $participant1->setSite($this->getReference("Vancouver", Site::class));
        $this->addReference("participant3", $participant3);
        $manager->persist($participant3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $ville1 = new Ville();
        $ville1->setNom("Rennes");
        $ville1->setCodePostal("35000");
        $this->addReference("Rennes", $ville1);
        $manager->persist($ville1);

        $ville2 = new Ville();
        $ville2->setNom("Nantes");
        $ville2->setCodePostal("44000");
        $this->addReference("Nantes", $ville2);
        $manager->persist($ville2);

        $ville3 = new Ville();
        $ville3->setNom("Vancouver");
        $ville3->setCodePostal("V5K 0A1");
        $this->addReference("Vancouver", $ville3);
        $manager->persist($ville3);

        $ville4 = new Ville();
        $ville4->setNom("St Herblain");
        $ville4->setCodePostal("44800");
        $this->addReference("St Herblain", $ville4);
        $manager->persist($ville4);

        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $site1 = new Site();
        $site1->setNom("Rennes");
        $this->addReference("Rennes", $site1);
        $manager->persist($site1);

        $site2 = new Site();
        $site2->setNom("Nantes");
        $this->addReference("Nantes", $site2);
        $manager->persist($site2);

        $site3 = new Site();
        $site3->setNom("Vancouver");
        $this->addReference("Vancouver", $site3);
        $manager->persist($site3);

        $manager->flush();
    }

}

<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $lieu1 = new Lieu();
        $lieu1->setNom("AVEC");
        $lieu1->setRue("1 rue du Breil");
        $lieu1->setVille($this->getReference("Rennes", Ville::class));
        $this->addReference("AVEC", $lieu1);
        $manager->persist($lieu1);

        $lieu2 = new Lieu();
        $lieu2->setNom("Le Labo");
        $lieu2->setRue("19 rue Léon Blum");
        $lieu2->setVille($this->getReference("Nantes", Ville::class));
        $this->addReference("Le Labo", $lieu2);
        $manager->persist($lieu2);

        $lieu3 = new Lieu();
        $lieu3->setNom("The Narrow Lounge");
        $lieu3->setRue("1899 Main St");
        $lieu3->setVille($this->getReference("Vancouver", Ville::class));
        $this->addReference("The Narrow Lounge", $lieu3);
        $manager->persist($lieu3);

        $lieu4 = new Lieu();
        $lieu4->setNom("ZBar");
        $lieu4->setRue("3 Impasse du Bourrelier");
        $lieu4->setVille($this->getReference("St Herblain", Ville::class));
        $this->addReference("ZBar", $lieu4);
        $manager->persist($lieu4);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VilleFixtures::class];
    }
}

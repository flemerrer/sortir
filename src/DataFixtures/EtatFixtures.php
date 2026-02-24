<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etat1 = new Etat();
        $etat1->setLibelle("Créée");
        $this->addReference('Créée', $etat1);
        $manager->persist($etat1);

        $etat2 = new Etat();
        $etat2->setLibelle("Ouverte");
        $this->addReference('Ouverte', $etat2);
        $manager->persist($etat2);

        $etat3 = new Etat();
        $etat3->setLibelle("Clôturée");
        $this->addReference('Clôturée', $etat3);
        $manager->persist($etat3);

        $etat4 = new Etat();
        $etat4->setLibelle("Activité en cours");
        $this->addReference('Activité en cours', $etat4);
        $manager->persist($etat4);

        $etat5 = new Etat();
        $etat5->setLibelle("Passée");
        $this->addReference('Passée', $etat5);
        $manager->persist($etat5);

        $etat6 = new Etat();
        $etat6->setLibelle("Annulée");
        $this->addReference('Annulée', $etat6);
        $manager->persist($etat6);

        $manager->flush();
    }
}

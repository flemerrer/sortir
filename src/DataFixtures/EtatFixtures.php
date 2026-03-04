<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Models\EtatLibelle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etat1 = new Etat();
        $etat1->setLibelle(EtatLibelle::CREEE->value);
        $this->addReference(EtatLibelle::CREEE->value, $etat1);
        $manager->persist($etat1);

        $etat2 = new Etat();
        $etat2->setLibelle(EtatLibelle::OUVERTE->value);
        $this->addReference(EtatLibelle::OUVERTE->value, $etat2);
        $manager->persist($etat2);

        $etat3 = new Etat();
        $etat3->setLibelle(EtatLibelle::CLOTUREE->value);
        $this->addReference(EtatLibelle::CLOTUREE->value, $etat3);
        $manager->persist($etat3);

        $etat4 = new Etat();
        $etat4->setLibelle(EtatLibelle::EN_COURS->value);
        $this->addReference(EtatLibelle::EN_COURS->value, $etat4);
        $manager->persist($etat4);

        $etat5 = new Etat();
        $etat5->setLibelle(EtatLibelle::PASSEE->value);
        $this->addReference(EtatLibelle::PASSEE->value, $etat5);
        $manager->persist($etat5);

        $etat6 = new Etat();
        $etat6->setLibelle(EtatLibelle::ANNULEE->value);
        $this->addReference(EtatLibelle::ANNULEE->value, $etat6);
        $manager->persist($etat6);

        $etat7 = new Etat();
        $etat7->setLibelle(EtatLibelle::ARCHIVEE->value);
        $this->addReference(EtatLibelle::ARCHIVEE->value, $etat7);
        $manager->persist($etat7);

        $manager->flush();
    }
}

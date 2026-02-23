<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $utilisateur = new Participant();
        $utilisateur->setNom("MASSIET");
        $utilisateur->setPrenom("JEAN");
        $utilisateur->setPseudo("backseat");
        $utilisateur->setEmail("backseat@email.tld");
        $utilisateur->setMotDePasse("password123");
        $utilisateur->setActif(true);

        $sortie = new Sortie();
        $sortie->setNom("Afterwork nems et ti ponche");
        $sortie->setDateHeureDebut(new \DateTimeImmutable("2026-09-31 18:30:00"));
        $sortie->setOrganisateur($utilisateur);
        $utilisateur->addSortie($sortie);

        $utilisateur->persist();
        $sortie->persist();

        $manager->flush();
    }
}

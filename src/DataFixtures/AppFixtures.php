<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création d'un site de rattachement
        $site = new Site();
        $site->setNom("SAINT HERBLAIN");

        // Création d'un utilisateur simple pour tester la connexion
        $utilisateur = new Participant();
        $utilisateur->setNom("MASSIET");
        $utilisateur->setPrenom("JEAN");
        $utilisateur->setPseudo("backseat");
        $utilisateur->setTelephone("0123456789");
        $utilisateur->setEmail("backseat@email.tld");
        $utilisateur->setMotDePasse("password123"); // Mot de passe en clair pour les tests
        $utilisateur->setAdministrateur(true);
        $utilisateur->setActif(true);

        // Création d'un administrateur pour tester la connexion
        $admin = new Participant();
        $admin->setNom("Admin");
        $admin->setPrenom("Admin");
        $admin->setPseudo("admin");
        $admin->setTelephone("0987654321");
        $admin->setEmail("admin@sortir.com");
        $admin->setMotDePasse("admin");  // Mot de passe en clair pour les tests
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $admin->setSite($site);

        // Création d'une sortie pour tester les fonctionnalités de base
        $sortie = new Sortie();
        $sortie->setNom("Afterwork nems et ti ponche");
        $sortie->setDateHeureDebut(new \DateTimeImmutable("2026-09-31 18:30:00"));
        $sortie->setDateLimiteInscription(new \DateTimeImmutable("2026-09-01"));
        $sortie->setDuree(240);
        $sortie->setNbInscriptionsMax(15);
        $sortie->setOrganisateur($utilisateur);
        $utilisateur->addSortieOrganisee($sortie);

        $manager->persist($site);
        $manager->persist($utilisateur);
        $manager->persist($admin);
        $manager->persist($sortie);
        $manager->flush();
    }
}

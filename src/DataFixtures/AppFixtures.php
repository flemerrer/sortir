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
        $manager->persist($site);

        // Création d'un utilisateur simple pour tester la connexion
        $utilisateur = new Participant();
        $utilisateur->setNom("MASSIET");
        $utilisateur->setPrenom("JEAN");
        $utilisateur->setPseudo("backseat");
        $utilisateur->setTelephone("0123456789");
        $utilisateur->setEmail("backseat@email.tld");
        $utilisateur->setMotdepasse("password123");  // Mot de passe en clair pour les tests
        $utilisateur->setAdministrateur(false);
        $utilisateur->setActif(true);
        $utilisateur->setSite($site);
        $manager->persist($utilisateur);

        // Création d'un administrateur pour tester la connexion
        $admin = new Participant();
        $admin->setNom("Admin");
        $admin->setPrenom("Admin");
        $admin->setPseudo("admin");
        $admin->setTelephone("0987654321");
        $admin->setEmail("admin@sortir.com");
        $admin->setMotdepasse("admin");  // Mot de passe en clair pour les tests
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $admin->setSite($site);
        $manager->persist($admin);

        // Note: Les sorties seront créées dans une prochaine étape
        // quand toutes les entités liées (Etat, Lieu, Ville) seront prêtes

        $manager->flush();
    }
}

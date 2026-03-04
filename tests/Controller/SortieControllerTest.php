<?php

namespace App\Tests\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use PHPUnit\Framework\TestCase;

class SortieControllerTest extends TestCase
{
    public function testSortieEntityCanBeCreated(): void
    {
        // Préparer
        $site = new Site();
        $site->setNom('Site Test');
        
        $ville = new Ville();
        $ville->setNom('Nantes');
        $ville->setCodePostal('44000');
        
        $lieu = new Lieu();
        $lieu->setNom('Lieu Test');
        $lieu->setRue('Rue Test');
        $lieu->setVille($ville);
        
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setSite($site);
        
        $sortie = new Sortie();
        $sortie->setNom('Sortie Test');
        $sortie->setDateHeureDebut(new \DateTimeImmutable('+7 days'));
        $sortie->setDuree(120);
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('+5 days'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setInfosSortie('Description');
        $sortie->setEtat($etat);
        $sortie->setLieu($lieu);
        $sortie->setSite($site);
        $sortie->setOrganisateur($participant);

        // Vérifier
        $this->assertEquals('Sortie Test', $sortie->getNom());
        $this->assertEquals(120, $sortie->getDuree());
        $this->assertEquals(10, $sortie->getNbInscriptionsMax());
        $this->assertEquals($participant, $sortie->getOrganisateur());
        $this->assertEquals($etat, $sortie->getEtat());
    }

    public function testParticipantCanBeAddedToSortie(): void
    {
        // Préparer
        $site = new Site();
        $site->setNom('Site Test');
        
        $ville = new Ville();
        $ville->setNom('Nantes');
        $ville->setCodePostal('44000');
        
        $lieu = new Lieu();
        $lieu->setNom('Lieu Test');
        $lieu->setRue('Rue Test');
        $lieu->setVille($ville);
        
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        
        $organisateur = new Participant();
        $organisateur->setPseudo('organisateur');
        $organisateur->setSite($site);
        
        $participant = new Participant();
        $participant->setPseudo('participant');
        $participant->setSite($site);
        
        $sortie = new Sortie();
        $sortie->setNom('Sortie Test');
        $sortie->setDateHeureDebut(new \DateTimeImmutable('+7 days'));
        $sortie->setDuree(120);
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('+5 days'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setInfosSortie('Description');
        $sortie->setEtat($etat);
        $sortie->setLieu($lieu);
        $sortie->setSite($site);
        $sortie->setOrganisateur($organisateur);

        // Exécuter
        $sortie->addParticipant($participant);

        // Vérifier
        $this->assertTrue($sortie->getParticipants()->contains($participant));
        $this->assertCount(1, $sortie->getParticipants());
    }

    public function testParticipantCanBeRemovedFromSortie(): void
    {
        // Préparer
        $site = new Site();
        $site->setNom('Site Test');
        
        $ville = new Ville();
        $ville->setNom('Nantes');
        $ville->setCodePostal('44000');
        
        $lieu = new Lieu();
        $lieu->setNom('Lieu Test');
        $lieu->setRue('Rue Test');
        $lieu->setVille($ville);
        
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        
        $organisateur = new Participant();
        $organisateur->setPseudo('organisateur');
        $organisateur->setSite($site);
        
        $participant = new Participant();
        $participant->setPseudo('participant');
        $participant->setSite($site);
        
        $sortie = new Sortie();
        $sortie->setNom('Sortie Test');
        $sortie->setDateHeureDebut(new \DateTimeImmutable('+7 days'));
        $sortie->setDuree(120);
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('+5 days'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setInfosSortie('Description');
        $sortie->setEtat($etat);
        $sortie->setLieu($lieu);
        $sortie->setSite($site);
        $sortie->setOrganisateur($organisateur);
        
        $sortie->addParticipant($participant);

        // Exécuter
        $sortie->removeParticipant($participant);

        // Vérifier
        $this->assertFalse($sortie->getParticipants()->contains($participant));
        $this->assertCount(0, $sortie->getParticipants());
    }
}

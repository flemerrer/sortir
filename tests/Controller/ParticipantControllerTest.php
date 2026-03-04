<?php

namespace App\Tests\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use PHPUnit\Framework\TestCase;

class ParticipantControllerTest extends TestCase
{
    public function testParticipantProfileDataIsCorrect(): void
    {
        // Préparation
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setNom('Dupont');
        $participant->setPrenom('Jean');
        $participant->setTelephone('0123456789');
        $participant->setEmail('jean@example.com');
        $participant->setSite($site);
        $participant->setActif(true);

        // Vérifications
        $this->assertEquals('testuser', $participant->getPseudo());
        $this->assertEquals('Dupont', $participant->getNom());
        $this->assertEquals('Jean', $participant->getPrenom());
        $this->assertEquals('0123456789', $participant->getTelephone());
        $this->assertEquals('jean@example.com', $participant->getEmail());
        $this->assertEquals($site, $participant->getSite());
        $this->assertTrue($participant->isActif());
    }

    public function testParticipantCanUpdateProfile(): void
    {
        // Préparation
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('oldpseudo');
        $participant->setNom('OldName');
        $participant->setPrenom('OldFirstName');
        $participant->setTelephone('0000000000');
        $participant->setEmail('old@example.com');
        $participant->setSite($site);

        // Mise à jour
        $participant->setPseudo('newpseudo');
        $participant->setNom('NewName');
        $participant->setPrenom('NewFirstName');
        $participant->setTelephone('1111111111');
        $participant->setEmail('new@example.com');

        // Vérifications
        $this->assertEquals('newpseudo', $participant->getPseudo());
        $this->assertEquals('NewName', $participant->getNom());
        $this->assertEquals('NewFirstName', $participant->getPrenom());
        $this->assertEquals('1111111111', $participant->getTelephone());
        $this->assertEquals('new@example.com', $participant->getEmail());
    }

    public function testParticipantCanChangePassword(): void
    {
        // Préparation
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setMotdepasse('oldhashedpassword');

        // Changement de mot de passe
        $participant->setMotdepasse('newhashedpassword');

        // Vérification
        $this->assertEquals('newhashedpassword', $participant->getMotdepasse());
    }
}

<?php

namespace App\Tests\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    public function testParticipantEntityCanBeAuthenticated(): void
    {
        // Préparer
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setNom('Test');
        $participant->setPrenom('User');
        $participant->setTelephone('0123456789');
        $participant->setEmail('test@example.com');
        $participant->setMotdepasse('hashedpassword');
        $participant->setActif(true);
        $participant->setSite($site);

        // Vérifier
        $this->assertEquals('testuser', $participant->getPseudo());
        $this->assertEquals('testuser', $participant->getUserIdentifier());
        $this->assertTrue($participant->isActif());
        $this->assertEquals('test@example.com', $participant->getEmail());
    }

    public function testInactiveParticipant(): void
    {
        // Préparer
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('inactiveuser');
        $participant->setNom('Inactive');
        $participant->setPrenom('User');
        $participant->setTelephone('0123456789');
        $participant->setEmail('inactive@example.com');
        $participant->setMotdepasse('hashedpassword');
        $participant->setActif(false);
        $participant->setSite($site);

        // Vérifier
        $this->assertFalse($participant->isActif());
    }

    public function testParticipantHasRoles(): void
    {
        // Préparer
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setActif(true);

        // Vérifier
        $this->assertIsArray($participant->getRoles());
        $this->assertContains('ROLE_USER', $participant->getRoles());
    }
}


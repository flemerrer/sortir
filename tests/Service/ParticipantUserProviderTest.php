<?php

namespace App\Tests\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\ParticipantRepository;
use App\Service\ParticipantUserProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantUserProviderTest extends TestCase
{
    private ParticipantRepository|MockObject $participantRepository;
    private ParticipantUserProvider $userProvider;

    protected function setUp(): void
    {
        $this->participantRepository = $this->createMock(ParticipantRepository::class);
        $this->userProvider = new ParticipantUserProvider($this->participantRepository);
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        // Préparation
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setActif(true);

        $this->participantRepository
            ->expects($this->once())
            ->method('findOneByPseudo')
            ->with('testuser')
            ->willReturn($participant);

        // Exécution
        $result = $this->userProvider->loadUserByIdentifier('testuser');

        // Vérification
        $this->assertInstanceOf(Participant::class, $result);
        $this->assertEquals('testuser', $result->getPseudo());
    }

    public function testLoadUserByIdentifierNotFound(): void
    {
        // Préparation
        $this->participantRepository
            ->expects($this->once())
            ->method('findOneByPseudo')
            ->with('nonexistent')
            ->willReturn(null);

        // Attente d'exception
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Participant "nonexistent" not found.');

        // Exécution
        $this->userProvider->loadUserByIdentifier('nonexistent');
    }

    public function testLoadUserByIdentifierInactiveUser(): void
    {
        // Préparation
        $participant = new Participant();
        $participant->setPseudo('inactiveuser');
        $participant->setActif(false);

        $this->participantRepository
            ->expects($this->once())
            ->method('findOneByPseudo')
            ->with('inactiveuser')
            ->willReturn($participant);

        // Attente d'exception
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Ce compte est inactif.');

        // Exécution
        $this->userProvider->loadUserByIdentifier('inactiveuser');
    }

    public function testRefreshUserSuccess(): void
    {
        // Préparation
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setSite($site);

        // Mock de l'ID via reflection
        $reflection = new \ReflectionClass($participant);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 1);

        $this->participantRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($participant);

        // Exécution
        $result = $this->userProvider->refreshUser($participant);

        // Vérification
        $this->assertInstanceOf(Participant::class, $result);
    }

    public function testRefreshUserWithInvalidType(): void
    {
        // Préparation
        $user = $this->createMock(UserInterface::class);

        // Attente d'exception
        $this->expectException(UnsupportedUserException::class);

        // Exécution
        $this->userProvider->refreshUser($user);
    }

    public function testRefreshUserNotFound(): void
    {
        // Préparation
        $participant = new Participant();
        
        // Mock de l'ID
        $reflection = new \ReflectionClass($participant);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 999);

        $this->participantRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Attente d'exception
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found.');

        // Exécution
        $this->userProvider->refreshUser($participant);
    }

    public function testSupportsClass(): void
    {
        // Test avec la classe Participant
        $this->assertTrue($this->userProvider->supportsClass(Participant::class));

        // Test avec une classe non supportée
        $this->assertFalse($this->userProvider->supportsClass(UserInterface::class));
        $this->assertFalse($this->userProvider->supportsClass(\stdClass::class));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

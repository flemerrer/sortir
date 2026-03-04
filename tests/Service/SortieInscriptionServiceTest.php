<?php

namespace App\Tests\Service;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Service\SortieInscriptionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SortieInscriptionServiceTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private SortieInscriptionService $inscriptionService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->inscriptionService = new SortieInscriptionService($this->entityManager);
    }

    private function createSortieOuverte(): Sortie
    {
        $etat = new Etat();
        $etat->setLibelle('Ouverte');
        
        $site = new Site();
        $site->setNom('Site Test');
        
        $organisateur = new Participant();
        $organisateur->setPseudo('organisateur');
        $organisateur->setSite($site);
        
        $sortie = new Sortie();
        $sortie->setNom('Sortie Test');
        $sortie->setEtat($etat);
        $sortie->setDateHeureDebut(new \DateTimeImmutable('+7 days'));
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('+5 days'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setOrganisateur($organisateur);
        $sortie->setSite($site);
        
        return $sortie;
    }

    private function createParticipant(string $pseudo = 'participant1'): Participant
    {
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo($pseudo);
        $participant->setSite($site);
        
        return $participant;
    }

    public function testInscrireParticipantSuccess(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();

        // Configuration du mock
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertTrue($result['success']);
        $this->assertEquals('Inscription réussie !', $result['message']);
        $this->assertTrue($sortie->getParticipants()->contains($participant));
    }

    public function testInscrireParticipantSortieNonOuverte(): void
    {
        // Préparation - sortie fermée
        $sortie = $this->createSortieOuverte();
        $etatFerme = new Etat();
        $etatFerme->setLibelle('Fermée');
        $sortie->setEtat($etatFerme);
        
        $participant = $this->createParticipant();

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Cette sortie n\'est pas ouverte aux inscriptions.', $result['message']);
    }

    public function testInscrireParticipantDateLimiteDepassee(): void
    {
        // Préparation - date limite dépassée
        $sortie = $this->createSortieOuverte();
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('-1 day'));
        
        $participant = $this->createParticipant();

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('La date limite d\'inscription est dépassée.', $result['message']);
    }

    public function testInscrireParticipantSortieComplete(): void
    {
        // Préparation - sortie avec nombre max de participants
        $sortie = $this->createSortieOuverte();
        $sortie->setNbInscriptionsMax(2);
        
        // Ajout de 2 participants (max atteint)
        $participant1 = $this->createParticipant('participant1');
        $participant2 = $this->createParticipant('participant2');
        $sortie->addParticipant($participant1);
        $sortie->addParticipant($participant2);
        
        $nouveauParticipant = $this->createParticipant('participant3');

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $nouveauParticipant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Il n\'y a plus de places disponibles pour cette sortie.', $result['message']);
    }

    public function testInscrireParticipantDejaInscrit(): void
    {
        // Préparation - participant déjà inscrit
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Vous êtes déjà inscrit à cette sortie.', $result['message']);
    }

    public function testInscrireOrganisateur(): void
    {
        // Préparation - l'organisateur tente de s'inscrire
        $sortie = $this->createSortieOuverte();
        $organisateur = $sortie->getOrganisateur();

        // Exécution
        $result = $this->inscriptionService->inscrireParticipant($sortie, $organisateur);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Vous êtes l\'organisateur de cette sortie, vous ne pouvez pas vous y inscrire.', $result['message']);
    }

    public function testDesinscrireParticipantSuccess(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);
        $participant->addSortieParticipee($sortie);

        // Configuration du mock
        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $result = $this->inscriptionService->desinscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertTrue($result['success']);
        $this->assertEquals('Désinscription réussie !', $result['message']);
        $this->assertFalse($sortie->getParticipants()->contains($participant));
    }

    public function testDesinscrireParticipantSortieNonOuverte(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $etatFerme = new Etat();
        $etatFerme->setLibelle('Clôturée');
        $sortie->setEtat($etatFerme);
        
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);

        // Exécution
        $result = $this->inscriptionService->desinscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Cette sortie n\'est pas ouverte, vous ne pouvez pas vous désinscrire.', $result['message']);
    }

    public function testDesinscrireParticipantDateLimiteDepassee(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $sortie->setDateLimiteInscription(new \DateTimeImmutable('-1 day'));
        
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);

        // Exécution
        $result = $this->inscriptionService->desinscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('La date limite d\'inscription est dépassée, vous ne pouvez plus vous désinscrire.', $result['message']);
    }

    public function testDesinscrireParticipantNonInscrit(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();

        // Exécution
        $result = $this->inscriptionService->desinscrireParticipant($sortie, $participant);

        // Vérifications
        $this->assertFalse($result['success']);
        $this->assertEquals('Vous n\'êtes pas inscrit à cette sortie.', $result['message']);
    }

    public function testPeutSinscrireReturnTrue(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();

        // Exécution
        $result = $this->inscriptionService->peutSinscrire($sortie, $participant);

        // Vérification
        $this->assertTrue($result);
    }

    public function testPeutSinscrireReturnFalseWhenAlreadyInscrit(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);

        // Exécution
        $result = $this->inscriptionService->peutSinscrire($sortie, $participant);

        // Vérification
        $this->assertFalse($result);
    }

    public function testPeutSeDesinscrireReturnTrue(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();
        $sortie->addParticipant($participant);

        // Exécution
        $result = $this->inscriptionService->peutSeDesinscrire($sortie, $participant);

        // Vérification
        $this->assertTrue($result);
    }

    public function testPeutSeDesinscrireReturnFalseWhenNotInscrit(): void
    {
        // Préparation
        $sortie = $this->createSortieOuverte();
        $participant = $this->createParticipant();

        // Exécution
        $result = $this->inscriptionService->peutSeDesinscrire($sortie, $participant);

        // Vérification
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

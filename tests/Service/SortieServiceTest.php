<?php

namespace App\Tests\Service;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Exception\SortieCancelException;
use App\Exception\SortieCreateException;
use App\Exception\SortieDeleteException;
use App\Exception\SortieFetchFilteredException;
use App\Exception\SortiePublishException;
use App\Exception\SortieUpdateException;
use App\Models\SortieDTO;
use App\Models\SortieSearchFilters;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\LieuService;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\InputBag;

class SortieServiceTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private LieuService|MockObject $lieuService;
    private EtatRepository|MockObject $etatRepository;
    private SortieRepository|MockObject $sortieRepository;
    private SortieService $sortieService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->lieuService = $this->createMock(LieuService::class);
        $this->etatRepository = $this->createMock(EtatRepository::class);
        $this->sortieRepository = $this->createMock(SortieRepository::class);
        
        $this->sortieService = new SortieService(
            $this->entityManager,
            $this->lieuService,
            $this->etatRepository,
            $this->sortieRepository
        );
    }

    private function createParticipant(): Participant
    {
        $site = new Site();
        $site->setNom('Site Test');
        
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setSite($site);
        
        return $participant;
    }

    private function createSortieDTO(): SortieDTO
    {
        $site = new Site();
        $site->setNom('Site Test');
        
        $lieu = new Lieu();
        $lieu->setNom('Lieu Test');
        $lieu->setRue('Rue Test');
        
        $dto = new SortieDTO();
        $dto->nom = 'Sortie Test';
        $dto->dateHeureDebut = new \DateTimeImmutable('+7 days');
        $dto->dateLimiteInscription = new \DateTimeImmutable('+5 days');
        $dto->nbInscriptionsMax = 10;
        $dto->duree = 120;
        $dto->infosSortie = 'Description de la sortie';
        $dto->site = $site;
        $dto->lieuxDisponibles = $lieu;
        
        return $dto;
    }

    public function testCreateSortieFromDTOSuccess(): void
    {
        // Préparation
        $dto = $this->createSortieDTO();
        $user = $this->createParticipant();
        
        $etatCree = new Etat();
        $etatCree->setLibelle('Créée');

        // Configuration des mocks
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Créée'])
            ->willReturn($etatCree);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Sortie::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $result = $this->sortieService->createSortieFromDTO($dto, $user);

        // Vérifications
        $this->assertInstanceOf(Sortie::class, $result);
        $this->assertEquals('Sortie Test', $result->getNom());
        $this->assertEquals($user, $result->getOrganisateur());
        $this->assertEquals($etatCree, $result->getEtat());
        $this->assertTrue($result->getParticipants()->contains($user));
    }

    public function testCreateSortieFromDTOWithNewLieu(): void
    {
        // Préparation
        $dto = $this->createSortieDTO();
        $dto->nomNouveauLieu = 'Nouveau Lieu';
        $dto->rueNouveauLieu = 'Nouvelle Rue';
        
        $user = $this->createParticipant();
        $etatCree = new Etat();
        $etatCree->setLibelle('Créée');
        
        $nouveauLieu = new Lieu();
        $nouveauLieu->setNom('Nouveau Lieu');

        // Configuration des mocks
        $this->lieuService
            ->expects($this->once())
            ->method('createLieuFromDTO')
            ->with($dto)
            ->willReturn($nouveauLieu);

        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Créée'])
            ->willReturn($etatCree);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $result = $this->sortieService->createSortieFromDTO($dto, $user);

        // Vérifications
        $this->assertEquals($nouveauLieu, $result->getLieu());
    }

    public function testCreateSortieFromDTOThrowsException(): void
    {
        // Préparation
        $dto = $this->createSortieDTO();
        $user = $this->createParticipant();

        // Configuration du mock pour lever une exception
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willThrowException(new \Exception('Database error'));

        // Attente d'exception
        $this->expectException(SortieCreateException::class);

        // Exécution
        $this->sortieService->createSortieFromDTO($dto, $user);
    }

    public function testUpdateSortieSuccess(): void
    {
        // Préparation
        $dto = $this->createSortieDTO();
        $sortie = new Sortie();
        $sortie->setNom('Ancien nom');

        // Configuration du mock
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $this->sortieService->updateSortie($dto, $sortie);

        // Vérifications
        $this->assertEquals('Sortie Test', $sortie->getNom());
        $this->assertEquals($dto->site, $sortie->getSite());
    }

    public function testUpdateSortieThrowsException(): void
    {
        // Préparation
        $dto = $this->createSortieDTO();
        $sortie = new Sortie();

        // Configuration du mock pour lever une exception
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Update error'));

        // Attente d'exception
        $this->expectException(SortieUpdateException::class);

        // Exécution
        $this->sortieService->updateSortie($dto, $sortie);
    }

    public function testPublishSortieSuccess(): void
    {
        // Préparation
        $sortie = new Sortie();
        $sortie->setNom('Sortie à publier');
        
        $etatOuvert = new Etat();
        $etatOuvert->setLibelle('Ouverte');

        // Configuration des mocks
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Ouverte'])
            ->willReturn($etatOuvert);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $this->sortieService->publishSortie($sortie);

        // Vérifications
        $this->assertEquals($etatOuvert, $sortie->getEtat());
    }

    public function testPublishSortieThrowsException(): void
    {
        // Préparation
        $sortie = new Sortie();

        // Configuration du mock pour lever une exception
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willThrowException(new \Exception('Publish error'));

        // Attente d'exception
        $this->expectException(SortiePublishException::class);

        // Exécution
        $this->sortieService->publishSortie($sortie);
    }

    public function testCancelSortieSuccess(): void
    {
        // Préparation
        $sortie = new Sortie();
        $sortie->setNom('Sortie à annuler');
        
        $etatAnnule = new Etat();
        $etatAnnule->setLibelle('Annulée');

        // Configuration des mocks
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['libelle' => 'Annulée'])
            ->willReturn($etatAnnule);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $this->sortieService->cancelSortie($sortie);

        // Vérifications
        $this->assertEquals($etatAnnule, $sortie->getEtat());
    }

    public function testCancelSortieThrowsException(): void
    {
        // Préparation
        $sortie = new Sortie();

        // Configuration du mock pour lever une exception
        $this->etatRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willThrowException(new \Exception('Cancel error'));

        // Attente d'exception
        $this->expectException(SortieCancelException::class);

        // Exécution
        $this->sortieService->cancelSortie($sortie);
    }

    public function testDeleteSortieSuccess(): void
    {
        // Préparation
        $sortie = new Sortie();
        $sortie->setNom('Sortie à supprimer');

        // Configuration des mocks
        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($sortie);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Exécution
        $this->sortieService->deleteSortie($sortie);
    }

    public function testDeleteSortieThrowsException(): void
    {
        // Préparation
        $sortie = new Sortie();

        // Configuration du mock pour lever une exception
        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->willThrowException(new \Exception('Delete error'));

        // Attente d'exception
        $this->expectException(SortieDeleteException::class);

        // Exécution
        $this->sortieService->deleteSortie($sortie);
    }

    public function testGetSortieWithFiltersSuccess(): void
    {
        // Préparation
        $user = $this->createParticipant();
        
        $inputBag = new InputBag([
            'site' => 1,
            'dateMin' => '2026-03-01',
            'dateMax' => '2026-03-31',
            'organisateur' => '1',
            'inscrit' => '1',
            'nonInscrit' => '0',
            'sortiesPassees' => '0',
            'recherche' => 'test'
        ]);

        $expectedSorties = [new Sortie(), new Sortie()];

        // Configuration du mock
        $this->sortieRepository
            ->expects($this->once())
            ->method('findSortieByFilters')
            ->with($this->callback(function (SortieSearchFilters $filters) use ($user) {
                return $filters->participant === $user
                    && $filters->siteId === 1
                    && $filters->recherche === 'test'
                    && $filters->organisateur === true
                    && $filters->inscrit === true;
            }))
            ->willReturn($expectedSorties);

        // Exécution
        $result = $this->sortieService->getSortieWithFilters($inputBag, $user);

        // Vérifications
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetSortieWithFiltersWithMinimalParameters(): void
    {
        // Préparation
        $user = $this->createParticipant();
        $inputBag = new InputBag([]);

        // Configuration du mock
        $this->sortieRepository
            ->expects($this->once())
            ->method('findSortieByFilters')
            ->willReturn([]);

        // Exécution
        $result = $this->sortieService->getSortieWithFilters($inputBag, $user);

        // Vérifications
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetSortieWithFiltersThrowsException(): void
    {
        // Préparation
        $user = $this->createParticipant();
        $inputBag = new InputBag([]);

        // Configuration du mock pour lever une exception
        $this->sortieRepository
            ->expects($this->once())
            ->method('findSortieByFilters')
            ->willThrowException(new \Exception('Filter error'));

        // Attente d'exception
        $this->expectException(SortieFetchFilteredException::class);

        // Exécution
        $this->sortieService->getSortieWithFilters($inputBag, $user);
    }

    public function testAddOrCreateLieuWithExistingLieu(): void
    {
        // Préparation
        $lieu = new Lieu();
        $lieu->setNom('Lieu existant');
        
        $dto = $this->createSortieDTO();
        $dto->lieuxDisponibles = $lieu;
        $dto->nomNouveauLieu = null;
        $dto->rueNouveauLieu = null;
        
        $sortie = new Sortie();

        // Exécution (méthode publique appelée via addOrCreateLieu)
        $this->sortieService->addOrCreateLieu($dto, $sortie);

        // Vérifications
        $this->assertEquals($lieu, $sortie->getLieu());
    }

    public function testAddOrCreateLieuWithNewLieu(): void
    {
        // Préparation
        $nouveauLieu = new Lieu();
        $nouveauLieu->setNom('Nouveau Lieu');
        
        $dto = $this->createSortieDTO();
        $dto->nomNouveauLieu = 'Nouveau Lieu';
        $dto->rueNouveauLieu = 'Nouvelle Rue';
        
        $sortie = new Sortie();

        // Configuration du mock
        $this->lieuService
            ->expects($this->once())
            ->method('createLieuFromDTO')
            ->with($dto)
            ->willReturn($nouveauLieu);

        // Exécution
        $this->sortieService->addOrCreateLieu($dto, $sortie);

        // Vérifications
        $this->assertEquals($nouveauLieu, $sortie->getLieu());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

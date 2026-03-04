<?php

namespace App\Tests\Service;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Models\SortieDTO;
use App\Service\LieuService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LieuServiceTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private LieuService $lieuService;

    protected function setUp(): void
    {
        // Création des mocks
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        // Création du service avec les dépendances mockées
        $this->lieuService = new LieuService($this->entityManager);
    }

    public function testCreateLieuFromDTO(): void
    {
        // Préparation des données
        $ville = new Ville();
        $ville->setNom('Nantes');
        $ville->setCodePostal('44000');
        
        $sortieDTO = new SortieDTO();
        $sortieDTO->nomNouveauLieu = 'Le Lieu Test';
        $sortieDTO->rueNouveauLieu = '123 Rue de Test';
        $sortieDTO->nouveauLieuLatitude = 47.2184;
        $sortieDTO->nouveauLieuLongitude = -1.5536;
        $sortieDTO->villesDisponibles = $ville;

        // Configuration des assertions sur le mock
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Lieu::class));

        // Exécution
        $lieu = $this->lieuService->createLieuFromDTO($sortieDTO);

        // Vérifications
        $this->assertInstanceOf(Lieu::class, $lieu);
        $this->assertEquals('Le Lieu Test', $lieu->getNom());
        $this->assertEquals('123 Rue de Test', $lieu->getRue());
        $this->assertEquals(47.2184, $lieu->getLatitude());
        $this->assertEquals(-1.5536, $lieu->getLongitude());
        $this->assertEquals('Nantes', $lieu->getVille());
    }

    public function testCreateLieuFromDTOWithNullCoordinates(): void
    {
        // Préparation des données
        $ville = new Ville();
        $ville->setNom('Paris');
        
        $sortieDTO = new SortieDTO();
        $sortieDTO->nomNouveauLieu = 'Lieu Sans Coordonnées';
        $sortieDTO->rueNouveauLieu = '456 Avenue Test';
        $sortieDTO->nouveauLieuLatitude = null;
        $sortieDTO->nouveauLieuLongitude = null;
        $sortieDTO->villesDisponibles = $ville;

        // Configuration des assertions
        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        // Exécution
        $lieu = $this->lieuService->createLieuFromDTO($sortieDTO);

        // Vérifications
        $this->assertNull($lieu->getLatitude());
        $this->assertNull($lieu->getLongitude());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

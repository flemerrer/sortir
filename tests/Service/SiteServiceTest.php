<?php

namespace App\Tests\Service;

use App\Entity\Site;
use App\Exception\SiteFetchException;
use App\Repository\SiteRepository;
use App\Service\SiteService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SiteServiceTest extends TestCase
{
    private SiteRepository|MockObject $siteRepository;
    private SiteService $siteService;

    protected function setUp(): void
    {
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->siteService = new SiteService($this->siteRepository);
    }

    public function testGetAllSitesSuccess(): void
    {
        // Préparation des données
        $site1 = new Site();
        $site1->setNom('Site 1');
        
        $site2 = new Site();
        $site2->setNom('Site 2');
        
        $expectedSites = [$site1, $site2];

        // Configuration du mock
        $this->siteRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedSites);

        // Exécution
        $result = $this->siteService->getAllSites();

        // Vérifications
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expectedSites, $result);
    }

    public function testGetAllSitesEmptyResult(): void
    {
        // Configuration du mock pour retourner un tableau vide
        $this->siteRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        // Exécution
        $result = $this->siteService->getAllSites();

        // Vérifications
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetAllSitesThrowsException(): void
    {
        // Configuration du mock pour lever une exception
        $this->siteRepository
            ->expects($this->once())
            ->method('findAll')
            ->willThrowException(new \Exception('Database error'));

        // Attente d'exception
        $this->expectException(SiteFetchException::class);
        $this->expectExceptionMessage('Erreur lors de la récupération des sites: Database error');

        // Exécution
        $this->siteService->getAllSites();
    }

    public function testGetAllSitesWithMultipleSites(): void
    {
        // Préparation de plusieurs sites
        $sites = [];
        for ($i = 1; $i <= 5; $i++) {
            $site = new Site();
            $site->setNom("Site $i");
            $sites[] = $site;
        }

        // Configuration du mock
        $this->siteRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($sites);

        // Exécution
        $result = $this->siteService->getAllSites();

        // Vérifications
        $this->assertCount(5, $result);
        foreach ($result as $index => $site) {
            $this->assertInstanceOf(Site::class, $site);
            $this->assertEquals("Site " . ($index + 1), $site->getNom());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

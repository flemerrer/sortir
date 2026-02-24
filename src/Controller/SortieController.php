<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{

    #[Route('/sorties', 'sortie_list')]
    public function list(SortieRepository $sortieRepository){
        $sorties = $sortieRepository->findAll();

        return $this->render('sorties/sorties.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
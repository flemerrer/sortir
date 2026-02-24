<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[\Symfony\Component\Routing\Annotation\Route('/sorties', 'sortie_list')]
    public function list(SortieRepository $sortieRepository){
        $sorties = $sortieRepository->findAll();

        return $this->render('/sorties/sorties.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    #[Route("/sorties/add", name: "app_sortie_add", methods: ["GET", "POST"])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $em->getRepository("App\Entity\Participant")->findOneById(1);
            $sortie->setOrganisateur($user);
            $sortie->setSite($user->getSite());
            $sortie->addParticipant($user);
            $sortie->setEtat($em->getRepository("App\Entity\Etat")->findOneBy(["libelle" => "Créée"]));
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute("app_main");
        }
        return $this->render("/sorties/add.html.twig", ["sortieForm" => $form]);
    }

    #[Route("/sorties/{id}", name: "app_sortie_read", methods: ["GET"])]
    public function read(Sortie $sortie): Response
    {
        return $this->render("/sorties/read.html.twig", [
            "sortie" => $sortie,
        ]);
    }

    #[Route("/sorties/{id}/edit", name: "app_sortie_edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }
        return $this->render("/sorties/edit.html.twig", [
            "sortie" => $sortie,
            "sortieForm" => $form,
        ]);
    }

    #[Route("/sorties/{id}/delete", name: "app_sortie_delete", methods: ["POST"])]
    public function delete(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $em->remove($sortie);
        $em->flush();
        return $this->redirectToRoute("app_sortie_list");
    }
}

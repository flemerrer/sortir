<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Exception\ParticipantCreateException;
use App\Form\ParticipantType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur de gestion des profils utilisateurs
 */
#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ParticipantController extends AbstractController
{
    /**
     * Afficher et modifier son propre profil
     */
    #[Route('/mon-profil', name: 'app_profil_mon_profil', methods: ['GET', 'POST'])]
    public function monProfil(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        /** @var Participant $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setMotdepasse($hashedPassword);
            }

            // Gestion de l'upload de photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    // On pourrait ajouter un champ photo dans l'entité Participant
                    // $user->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors de l\'upload de la photo');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès');

            return $this->redirectToRoute('app_profil_mon_profil');
        }

        return $this->render('profil/mon_profil.html.twig', [
            'form' => $form,
            'participant' => $user,
        ]);
    }

    /**
     * Afficher le profil d'un autre participant
     */
    #[Route('/{id}', name: 'app_profil_afficher', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function afficherProfil(
        Participant $participant
    ): Response {
        return $this->render('profil/afficher_profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * Ajouter un participant manuellement
     */
    #[Route('/add-participant', name: 'app_add_participant', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_ADMIN')]
    public function ajouterParticipant(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $createParticipant = new Participant();
        $form = $this->createForm(ParticipantType::class, $createParticipant);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            try{
                $participant = $form->getData();
                $plainPassword = $form->get('motdepasse')->getData();
                $hashedPassword = $passwordHasher->hashPassword($participant, $plainPassword);
                $participant->setMotdepasse($hashedPassword);
                $em->persist($participant);
                $em->flush();
                $this->addFlash('success', 'Le participant a bien été ajouté avec succès.');
                return $this->redirectToRoute('app_list_participant');
            }catch(ParticipantCreateException $e){
                $this->addFlash("error", $e->getMessage());
            }
        }
        return $this->render('participant/add-participant.html.twig',[
            "participantForm" => $form->createView(),
        ]);
    }

    #[Route('/list-participant', name:'app_list_participant', methods:["GET"])]
    #[IsGranted('ROLE_ADMIN')]
    public function listParticipant(ParticipantRepository $participantRepository): Response
    {
        $participants = $participantRepository->findAll();
        return $this->render('/participant/list-participant.html.twig', [
            'participants' => $participants
        ]);
    }
}

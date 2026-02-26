<?php

    namespace App\Controller;

    use App\Entity\Lieu;
    use App\Entity\Sortie;
    use App\Form\SortieType;
    use App\Models\SortieDTO;
    use App\Repository\EtatRepository;
    use App\Repository\SortieRepository;
    use App\Service\SortieInscriptionService;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    final class SortieController extends AbstractController
    {
        /**
         * @param SortieRepository $sortieRepository
         * @return Response
         */
        #[\Symfony\Component\Routing\Annotation\Route('/sorties', 'app_sortie_list')]
        public function list(SortieRepository $sortieRepository)
        {
            $sorties = $sortieRepository->findAll();

            return $this->render('/sorties/sorties.html.twig', [
                'sorties' => $sorties,
            ]);
        }

        /**
         * @param Request $request
         * @param EtatRepository $etatRepository
         * @param EntityManagerInterface $em
         * @return Response
         */
        #[Route("/sorties/add", name: "app_sortie_add", methods: ["GET", "POST"])]
        public function create(Request $request, EtatRepository $etatRepository, EntityManagerInterface $em): Response
        {
            $createSortieDTO = new SortieDTO();
            $form = $this->createForm(SortieType::class, $createSortieDTO);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $sortieDTO = $form->getData();
                $user = $this->getUser();
                try {
                    $sortie = $this->createSortie($sortieDTO);
                    $this->addOrCreateLieu($sortieDTO, $sortie, $em);
                    $sortie->setOrganisateur($user);
                    $sortie->addParticipant($user);
                    $sortie->setSite($user->getSite());
                    $sortie->setEtat($etatRepository->findOneBy(["libelle" => "Créée"]));
                    $em->persist($sortie);
                    $em->flush();
                    $this->addFlash("success", "Sortie créée avec succès.");
                    return $this->redirectToRoute("app_sortie_list");
                } catch (\Exception $e) {
                    $this->addFlash("error", "Une erreur est survenue lors de la création de la sortie.");
                }
            }
            return $this->render("/sorties/addOrEdit.html.twig", [
                "sortieForm" => $form,
            ]);
        }

        /**
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}", name: "app_sortie_read", methods: ["GET"])]
        public function read(Sortie $sortie): Response
        {
            $user = $this->getUser();
            $editionAllowed = $this->isGranted("ROLE_ADMIN") || $user === $sortie->getOrganisateur();
            return $this->render("/sorties/read.html.twig", compact("sortie", "editionAllowed"));
        }

        /**
         * @param Request $request
         * @param Sortie $sortie
         * @param EntityManagerInterface $em
         * @return Response
         */
        #[Route("/sorties/{id}/edit", name: "app_sortie_edit", methods: ["GET", "POST"])]
        public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
        {
            if ($this->isGranted("ROLE_ADMIN") || $this->getUser() === $sortie->getOrganisateur()) {
                $editSortieDTO = new SortieDTO();
                $editSortieDTO->loadSortie($sortie);
                $form = $this->createForm(SortieType::class, $editSortieDTO);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $sortieDTO = $form->getData();
                    try {
                        $this->updateSortie($sortieDTO, $sortie);
                        $this->addOrCreateLieu($sortieDTO, $sortie, $em);
                        $em->flush();
                        $this->addFlash("success", "Sortie modifiée avec succès.");
                        return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
                    } catch (\Exception $e) {
                        $this->addFlash("error", "Une erreur est survenue lors de la modification de la sortie.");
                    }
                }
                return $this->render("/sorties/addOrEdit.html.twig", [
                    "sortie" => $sortie,
                    "sortieForm" => $form,
                ]);
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de modifier cette sortie.");
                return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
            }
        }

        /**
         * @param Sortie $sortie
         * @param EntityManagerInterface $em
         * @return Response
         */
        #[Route("/sorties/{id}/delete", name: "app_sortie_delete", methods: ["POST"])]
        public function delete(Sortie $sortie, EntityManagerInterface $em): Response
        {
            if ($this->isGranted("ROLE_ADMIN") || $this->getUser() === $sortie->getOrganisateur()) {
                try {
                    $em->remove($sortie);
                    $em->flush();
                    $this->addFlash("success", "Sortie supprimée avec succès.");
                    return $this->redirectToRoute("app_sortie_list");
                } catch (\Exception $e) {
                    $this->addFlash("error", "Une erreur est survenue lors de la suppression de la sortie.");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de supprimer cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }

        /**
         * @param SortieDTO $sortieDTO
         * @param Sortie $sortie
         * @param EntityManagerInterface $em
         * @return void
         */
        public function addOrCreateLieu(mixed $sortieDTO, $sortie, $em): void
        {
            if ($sortieDTO->nomNouveauLieu && $sortieDTO->rueNouveauLieu) {
                $lieu = new Lieu();
                $lieu->setNom($sortieDTO->nomNouveauLieu);
                $lieu->setRue($sortieDTO->rueNouveauLieu);
                $lieu->setVille($sortieDTO->villesDisponibles);
                $em->persist($lieu);
                $sortie->setLieu($lieu);
            } else {
                $sortie->setLieu($sortieDTO->lieuxDisponibles);
            }
        }

        /**
         * @param SortieDTO $dto
         * @param Sortie $sortie
         * @return void
         */
        private function updateSortie(SortieDTO $dto, Sortie $sortie): void
        {
            $sortie->setNom($dto->nom);
            $sortie->setDuree($dto->duree);
            $sortie->setDateHeureDebut($dto->dateHeureDebut);
            $sortie->setDateLimiteInscription($dto->dateLimiteInscription);
            $sortie->setNbInscriptionsMax($dto->nbInscriptionsMax);
            $sortie->setInfosSortie($dto->infosSortie);
            $sortie->setLieu($dto->lieu);
        }

        /**
         * @param mixed $sortieDTO
         * @return Sortie
         */
        public function createSortie(mixed $sortieDTO): Sortie
        {
            $sortie = new Sortie();
            $sortie->setNom($sortieDTO->nom);
            $sortie->setDateHeureDebut($sortieDTO->dateHeureDebut);
            $sortie->setDuree($sortieDTO->duree);
            $sortie->setDateLimiteInscription($sortieDTO->dateLimiteInscription);
            $sortie->setNbInscriptionsMax($sortieDTO->nbInscriptionsMax);
            $sortie->setInfosSortie($sortieDTO->infosSortie);
            return $sortie;
        }

        /**
         * Inscrire un participant à une sortie
         *
         * @param Sortie $sortie
         * @param SortieInscriptionService $inscriptionService
         * @return Response
         */
        #[Route("/sorties/{id}/inscription", name: "app_sortie_inscription", methods: ["POST"])]
        public function inscrire(Sortie $sortie, SortieInscriptionService $inscriptionService): Response
        {
            // L'utilisateur est forcément connecté grâce à la configuration security.yaml
            $participant = $this->getUser();

            $result = $inscriptionService->inscrireParticipant($sortie, $participant);
            
            if ($result['success']) {
                $this->addFlash('success', $result['message']);
            } else {
                $this->addFlash('error', $result['message']);
            }

            return $this->redirectToRoute('app_sortie_list');
        }

        /**
         * Désinscrire un participant d'une sortie
         *
         * @param Sortie $sortie
         * @param SortieInscriptionService $inscriptionService
         * @return Response
         */
        #[Route("/sorties/{id}/desinscription", name: "app_sortie_desinscription", methods: ["POST"])]
        public function desinscrire(Sortie $sortie, SortieInscriptionService $inscriptionService): Response
        {
            // L'utilisateur est forcément connecté grâce à la configuration security.yaml
            $participant = $this->getUser();

            $result = $inscriptionService->desinscrireParticipant($sortie, $participant);
            
            if ($result['success']) {
                $this->addFlash('success', $result['message']);
            } else {
                $this->addFlash('error', $result['message']);
            }

            return $this->redirectToRoute('app_sortie_list');
        }
    }

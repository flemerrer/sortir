<?php

    namespace App\Controller;

    use App\Entity\Sortie;
    use App\Exception\SiteFetchException;
    use App\Exception\SortieCancelException;
    use App\Exception\SortieCreateException;
    use App\Exception\SortieFetchFilteredException;
    use App\Exception\SortiePublishException;
    use App\Exception\SortieUpdateException;
    use App\Exception\SortieDeleteException;
    use App\Form\SortieType;
    use App\Models\SortieDTO;
    use App\Service\SiteService;
    use App\Service\SortieInscriptionService;
    use App\Service\SortieService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;

    /**
     * Controller responsable des routes liées aux Sorties
     */
    final class SortieController extends AbstractController
    {
        public function __construct(
            private readonly SortieService $sortieService
        )
        {
        }

        /**
         * @return Response
         * @throws SiteFetchException
         */
        #[Route('/sorties', name: 'app_sortie_list')]
 public function list(Request $request, SiteService $siteService)
        {
            try {
                $sites = $siteService->getAllSites();
                $sorties = $this->sortieService->getSortieWithFilters($request->query, $this->getUser());

                return $this->render('/sorties/sorties.html.twig', [
                    'sorties' => $sorties,
                    'sites' => $sites
                ]);
            } catch (SortieFetchFilteredException $e) {
                $this->addFlash('error', 'Erreur lors de la récupération des sorties : ' . $e->getMessage());
            } catch (SiteFetchException $e) {
                $this->addFlash('error', 'Erreur lors de la récupération des sites : ' . $e->getMessage());
            }
            return $this->render('/sorties/sorties.html.twig', [
                'sorties' => [],
                'sites' => []
            ]);
        }

        /**
         * @param Request $request
         * @return Response
         */
        #[Route("/sorties/add", name: "app_sortie_add", methods: ["GET", "POST"])]
        public function create(Request $request): Response
        {
            $createSortieDTO = new SortieDTO();
            $form = $this->createForm(SortieType::class, $createSortieDTO);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $sortieDTO = $form->getData();
                    $user = $this->getUser();
                    $sortie = $this->sortieService->createSortieFromDTO($sortieDTO, $user);
                    $this->addFlash("success", "Sortie créée avec succès.");
                    return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
                } catch (SortieCreateException $e) {
                    $this->addFlash("error", "Erreur lors de la création de la sortie : {$e->getMessage()}");
                }
            }
            return $this->render("/sorties/addOrEdit.html.twig", [
                "sortieForm" => $form->createView(),
            ]);
        }

        /**
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}", name: "app_sortie_read", methods: ["GET"])]
        public function read(Sortie $sortie): Response
        {
            $userCanEdit = $this->userCanEdit($sortie);
            return $this->render("/sorties/read.html.twig", compact("sortie", "userCanEdit"));
        }

        /**
         * @param Request $request
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}/edit", name: "app_sortie_edit", methods: ["GET", "POST"])]
        public function edit(Request $request, Sortie $sortie) : Response
        {
            if ($this->userCanEdit($sortie)) {
                $editSortieDTO = new SortieDTO();
                $editSortieDTO->loadSortie($sortie);
                $form = $this->createForm(SortieType::class, $editSortieDTO);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    try {
                        $sortieDTO = $form->getData();
                        $this->sortieService->updateSortie($sortieDTO, $sortie);
                        $this->addFlash("success", "Sortie modifiée avec succès.");
                        return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
                    } catch (SortieUpdateException $e) {
                        $this->addFlash("error", "Erreur lors de la modification de la sortie : {$e->getMessage()}");
                    }
                }
                return $this->render("/sorties/addOrEdit.html.twig", [
                    "sortie" => $sortie,
                    "sortieForm" => $form->createView(),
                ]);
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de modifier cette sortie.");
                return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
            }
        }

        /**
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}/publish", name: "app_sortie_publish", methods: ["GET"])]
        public function publish(Sortie $sortie): Response
        {
            if ($this->userCanEdit($sortie) &&  $sortie->getEtat()->getLibelle() === "Créée") {
                try {
                    $this->sortieService->publishSortie($sortie);
                    $this->addFlash("success", "Sortie publiée avec succès.");
                } catch (SortiePublishException $e) {
                    $this->addFlash("error", "Erreur lors de la publication de la sortie : {$e->getMessage()}");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de publier cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }

        /**
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}/cancel", name: "app_sortie_cancel", methods: ["GET"])]
        public function cancel(Sortie $sortie) : Response
        {
            if ($this->userCanEdit($sortie) && $sortie->isCancellable()) {
                try {
                    $this->sortieService->cancelSortie($sortie);
                    $this->addFlash("success", "Sortie annulée avec succès.");
                } catch (SortieCancelException $e) {
                    $this->addFlash("error", "Erreur lors de l'annulation de la sortie : {$e->getMessage()}");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission d'annuler cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }

        /**
         * @param Sortie $sortie
         * @return Response
         */
        #[Route("/sorties/{id}/delete", name: "app_sortie_delete", methods: ["POST"])]
        public function delete(Sortie $sortie): Response
        {
            if ($this->userCanEdit($sortie)) {
                try {
                    $this->sortieService->deleteSortie($sortie);
                    $this->addFlash("success", "Sortie supprimée avec succès.");
                    return $this->redirectToRoute("app_sortie_list");
                } catch (SortieDeleteException $e) {
                    $this->addFlash("error", "Erreur lors de la suppression de la sortie : {$e->getMessage()}");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de supprimer cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
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

        private function userCanEdit(Sortie $sortie)
        {
            return $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
        }
    }

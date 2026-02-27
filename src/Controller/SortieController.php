<?php

    namespace App\Controller;

    use App\Entity\Sortie;
    use App\Form\SortieType;
    use App\Models\SortieDTO;
    use App\Repository\EtatRepository;
    use App\Service\SortieInscriptionService;
    use App\Service\SortieManagerService;
    use App\Repository\SiteRepository;
    use App\Repository\SortieRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    /**
     * Controller responsable des routes liées aux Sorties
     */
    final class SortieController extends AbstractController
    {

        public function __construct(
            private SortieManagerService $sortieManager
        ) {
        }

        /**
         * @param SortieRepository $sortieRepository
         * @return Response
         */
        #[\Symfony\Component\Routing\Annotation\Route('/sorties', 'app_sortie_list')]
        public function list(Request $request, SortieRepository $sortieRepository, SiteRepository $siteRepository)
        {
            $siteId = $request->query->getInt('site') ?: null;
            $dateMin = $request->query->get('dateMin');
            $dateMax = $request->query->get('dateMax');
            $organisateur = $request->query->get('organisateur');
            $inscrit = $request->query->get('inscrit');
            $nonInscrit = $request->query->get('nonInscrit');
            $sortiesPassees = $request->query->get('sortiesPassees');
            $recherche = $request->query->get('recherche');

            $sorties = $sortieRepository->findSortieByFilters($siteId, $dateMin ? new \DateTime($dateMin) : null, $dateMax ? new \DateTime($dateMax) : null, $organisateur, $inscrit, $nonInscrit, $sortiesPassees, $this->getUser(), $recherche);
            

            $sites = $siteRepository->findAll();
            return $this->render('/sorties/sorties.html.twig', [
                'sorties' => $sorties,
                'sites' => $sites
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
                    $sortie = $this->sortieManager->createSortie($sortieDTO);
                    $this->sortieManager->addOrCreateLieu($sortieDTO, $sortie, $em);
                    $sortie->setOrganisateur($user);
                    $sortie->addParticipant($user);
                    $sortie->setSite($user->getSite());
                    $sortie->setEtat($etatRepository->findOneBy(["libelle" => "Créée"]));
                    $em->persist($sortie);
                    $em->flush();
                    $this->addFlash("success", "Sortie créée avec succès.");
                    return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
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
            $userCanEdit = $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
            return $this->render("/sorties/read.html.twig", compact("sortie", "userCanEdit"));
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
            $userCanEdit = $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
            if ($userCanEdit) {
                $editSortieDTO = new SortieDTO();
                $editSortieDTO->loadSortie($sortie);
                $form = $this->createForm(SortieType::class, $editSortieDTO);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $sortieDTO = $form->getData();
                    try {
                        $this->sortieManager->updateSortie($sortieDTO, $sortie);
                        $this->sortieManager->addOrCreateLieu($sortieDTO, $sortie, $em);
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
        #[Route("/sorties/{id}/publish", name: "app_sortie_publish", methods: ["GET"])]
        public function publish(Sortie $sortie, EntityManagerInterface $em): Response
        {
            $userCanEdit = $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
            if ($userCanEdit &&  $sortie->getEtat()->getLibelle() === "Créée") {
                try {
                    $newEtat = $em->getRepository("App\Entity\Etat")->findOneBy(["libelle" => "Ouverte"]);
                    $sortie->setEtat($newEtat);
                    $em->flush();
                    $this->addFlash("success", "Sortie publiée avec succès.");
                } catch (\Exception $e) {
                    $this->addFlash("error", "Une erreur est survenue lors de la publication de la sortie.");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission de publier cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }

        /**
         * @param Sortie $sortie
         * @param EntityManagerInterface $em
         * @return Response
         */
        #[Route("/sorties/{id}/cancel", name: "app_sortie_cancel", methods: ["GET"])]
        public function cancel(Sortie $sortie, EntityManagerInterface $em): Response
        {
            $userCanEdit = $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
            $currentEtat = $sortie->getEtat()->getLibelle();
            $statusOk = in_array($currentEtat, ["Créée", "Ouverte", "Clôturée"]);
            if ($userCanEdit && $statusOk) {
                try {
                    $newEtat = $em->getRepository("App\Entity\Etat")->findOneBy(["libelle" => "Annulée"]);
                    $sortie->setEtat($newEtat);
                    $em->flush();
                    $this->addFlash("success", "Sortie annulée avec succès.");
                } catch (\Exception $e) {
                    $this->addFlash("error", "Une erreur est survenue lors de l'annulation de la sortie.");
                }
            } else {
                $this->addFlash("error", "Vous n'avez pas la permission d'annuler cette sortie.");
            }
            return $this->redirectToRoute("app_sortie_read", ["id" => $sortie->getId()]);
        }

        /**
         * @param Sortie $sortie
         * @param EntityManagerInterface $em
         * @return Response
         */
        #[Route("/sorties/{id}/delete", name: "app_sortie_delete", methods: ["POST"])]
        public function delete(Sortie $sortie, EntityManagerInterface $em): Response
        {
            $userCanEdit = $this->isGranted("ROLE_ADMIN") || $sortie->isOrganisateur($this->getUser());
            if ($userCanEdit) {
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

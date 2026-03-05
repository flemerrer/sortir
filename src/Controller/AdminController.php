<?php

    namespace App\Controller;

    use App\Entity\UserFileUpdloadRecord;
    use App\Exception\CsvParsingException;
    use App\Exception\CsvUploadException;
    use App\Exception\ParticipantBatchPersistException;
    use App\Form\FileUploadType;
    use App\Repository\ParticipantRepository;
    use App\Service\CsvFileService;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
    use Symfony\Component\String\Slugger\SluggerInterface;

    /**
     * Contrôleur pour les routes admin
     */
    class AdminController extends AbstractController
    {
        /**
         * Page d'import des utilisateurs
         */
        #[Route("/admin/import", name: "app_admin_import")]
        public function import(
            Request        $request,
            CsvFileService $csvFileService
        ): Response
        {
            $uploadRecord = new UserFileUpdloadRecord();
            $form = $this->createForm(FileUploadType::class, $uploadRecord);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get("csvFile")->getData();
                try {
                    $newFileName = $csvFileService->upload($file);
                    $user = $this->getUser();
                    $users = $csvFileService->extractUsers($newFileName);
                    $csvFileService->createMultipleUsers($users);
                    $csvFileService->saveEvent($uploadRecord, $user, $newFileName);
                } catch (CsvUploadException|CsvParsingException|ParticipantBatchPersistException $e) {
                    $this->addFlash("error", $e->getMessage());
                }
            }
            return $this->render('/admin/import_users.html.twig', [
                'form' => $form
            ]);
        }

    }

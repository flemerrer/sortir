<?php

    namespace App\Service;

    use App\Entity\UserFileUpdloadRecord;
    use App\Exception\CsvUploadException;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\String\Slugger\SluggerInterface;

    class CsvFileService
    {
        public function __construct(
            private readonly string  $targetDir,
            private readonly SluggerInterface $slugger,
            private readonly LoggerInterface  $logger,
            private readonly EntityManagerInterface $em
        )
        {
        }

        /**
         * @throws CsvUploadException
         */
        public function upload(UploadedFile $file): string
        {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $this->slugger->slug($originalFilename);
            $newFileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->targetDir, $newFileName);
            } catch (FileException $e) {
                $this->logger->error("Erreur lors du transfert du fichier csv : {$e->getMessage()}");
                throw new CsvUploadException($e);
            }
        }

        public function saveEvent(UserFileUpdloadRecord $record, UserInterface $user, string $newFileName)
        {
            $record->setUser($user);
            $record->setFileName($newFileName);
            $record->setUploadDate(new \DateTimeImmutable());
            try {
            $this->em->persist($record);
            $this->em->flush();
            } catch (\Exception $e) {
                $this->logger->error("Erreur lors de la persistance de l'événement d'import utilisateurs : {$e->getMessage()}");
            }
        }

        public function extractUsers(string $newFileName)
        {
        }
    }

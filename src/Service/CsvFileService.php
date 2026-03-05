<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Entity\UserFileUpdloadRecord;
    use App\Exception\CsvBadFormatException;
    use App\Exception\CsvParsingException;
    use App\Exception\CsvUploadException;
    use App\Exception\ParticipantBatchPersistException;
    use Doctrine\ORM\EntityManagerInterface;
    use League\Csv\Exception;
    use League\Csv\InvalidArgument;
    use League\Csv\Reader;
    use League\Csv\UnavailableStream;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\String\Slugger\SluggerInterface;

    //TODO: Add missing unit tests and integration test for upload route

    /**
     * Service responsable de l'import des utilisateurs depuis un fichier CSV
     */
    class CsvFileService
    {
        /**
         * @param string $targetDir
         * @param SluggerInterface $slugger
         * @param LoggerInterface $logger
         * @param EntityManagerInterface $em
         */
        public function __construct(
            private readonly string                 $targetDir,
            private readonly SluggerInterface       $slugger,
            private readonly LoggerInterface        $logger,
            private readonly EntityManagerInterface $em
        )
        {
        }

        /**
         * @param UploadedFile $file
         * @return string
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

        /**
         * @param string $newFileName
         * @return array
         * @throws CsvBadFormatException
         * @throws CsvParsingException
         */
        public function extractUsers(string $newFileName): array
        {
            $target = "{$this->targetDir}\{$newFileName}";
            try {
                $template = ["pseudo", "nom", "prenom", "telephone", "email", "motdepass"];
                $reader = Reader::from($target, 'r');
                $reader->setDelimiter(';');
                $reader->setHeaderOffset(0);
                $records = $reader->getRecords();
                $header =  $reader->getHeader();
                if ($header != $template){
                    $this->logger->error("Fichier csv dans le mauvais format. Interrupting process.");
                    throw new CsvBadFormatException();
                }
                $array = null;
                foreach ($records as $record) {
                    $user = new Participant();
                    $user->setPseudo($record[0]);
                    $user->setNom($record[1]);
                    $user->setPrenom($record[2]);
                    $user->setTelephone($record[3]);
                    $user->setEmail($record[4]);
                    $user->setMotdepasse($record[5]);
                    $array[] = $user;
                }
                return $array;
            } catch (UnavailableStream|InvalidArgument $e) {
                $this->logger->error("Erreur lors du chargement du fichier csv : {$e->getMessage()}");
                throw new CsvParsingException();
            } catch (Exception $e) {
                $this->logger->error("Erreur lors de la lecture du fichier csv : {$e->getMessage()}");
                throw new CsvParsingException();
            }
        }

        /**
         * @param array $users
         * @return void
         * @throws ParticipantBatchPersistException
         */
        public function createMultipleUsers(array $users): void
        {
            try {
                $batchSize = 20;
                $i = 1;
                foreach ($users as $user) {
                    $this->em->persist($user);
                    $i++;
                    if (($i % $batchSize) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                        // Important somehow according to documentation (memory leaks)
                        // See: https://www.doctrine-project.org/projects/doctrine-orm/en/3.6/reference/batch-processing.html
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error("Erreur lors de la creation des utilisateurs depuis les données : {$e->getMessage()}");
                throw new ParticipantBatchPersistException();
            }
        }

        /**
         * @param UserFileUpdloadRecord $record
         * @param UserInterface $user
         * @param string $newFileName
         * @return void
         */
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
    }

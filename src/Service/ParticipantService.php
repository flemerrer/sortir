<?php

    namespace App\Service;

    use App\Entity\Participant;
    use App\Exception\ParticipantCreateException;
    use App\Repository\ParticipantRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    /**
     * Service responsable de la gestion des participants
     */
    class ParticipantService
    {
        
        public function __construct(
            private readonly EntityManagerInterface $em,
            private readonly ParticipantRepository $participant_repository,
            private readonly LoggerInterface $logger,
            private readonly UserPasswordHasherInterface $passwordHasher
        )
        {
        }

        public function getAllParticipants(): array
        {
             return $this->participant_repository->findAll();
        }

        public function createParticipants(Participant $participant, string $plainPassword): void
        {
            try{
                $hashedPassword = $this->passwordHasher->hashPassword($participant, $plainPassword);

                $participant->setMotdepasse($hashedPassword);

                $this->em->persist($participant);
                $this->em->flush();

            }catch(\Exception $e) {
                $this->logger->error('Error creating participant: ' . $e->getMessage(), ['exception' => $e]);
                throw new ParticipantCreateException();
            }
        }

    }
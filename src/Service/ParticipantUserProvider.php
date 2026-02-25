<?php

namespace App\Service;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Service de la couche métier qui charge les utilisateurs (Participants) depuis la base de données
 */
class ParticipantUserProvider implements UserProviderInterface
{
    public function __construct(
        private ParticipantRepository $participantRepository
    ) {
    }

    /**
     * Charge un utilisateur par son identifiant (pseudo)
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $participant = $this->participantRepository->findOneByPseudo($identifier);

        if (!$participant) {
            throw new UserNotFoundException(sprintf('Participant "%s" not found.', $identifier));
        }

        // Vérifier que le participant est actif
        if (!$participant->isActif()) {
            throw new UserNotFoundException('Ce compte est inactif.');
        }

        return $participant;
    }

    /**
     * Rafraîchit l'utilisateur depuis la base de données
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        $refreshedUser = $this->participantRepository->find($user->getId());

        if (!$refreshedUser) {
            throw new UserNotFoundException('User not found.');
        }

        return $refreshedUser;
    }

    /**
     * Vérifie si ce provider supporte la classe d'utilisateur donnée
     */
    public function supportsClass(string $class): bool
    {
        return Participant::class === $class || is_subclass_of($class, Participant::class);
    }
}

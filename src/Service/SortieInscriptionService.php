<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de la couche métier qui gère les inscriptions aux sorties
 */
class SortieInscriptionService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Inscrit un participant à une sortie
     * 
     * @param Sortie $sortie La sortie à laquelle s'inscrire
     * @param Participant $participant Le participant qui s'inscrit
     * @return array ['success' => bool, 'message' => string]
     */
    public function inscrireParticipant(Sortie $sortie, Participant $participant): array
    {
        // Vérifier que la sortie est dans l'état "Ouverte"
        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return [
                'success' => false,
                'message' => 'Cette sortie n\'est pas ouverte aux inscriptions.'
            ];
        }

        // Vérifier que la date limite d'inscription n'est pas dépassée
        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            return [
                'success' => false,
                'message' => 'La date limite d\'inscription est dépassée.'
            ];
        }

        // Vérifier qu'il reste des places disponibles
        // Note: Une sortie complète devrait normalement avoir le statut "Clôturée"
        // Ce check est une sécurité supplémentaire (défense en profondeur)
        $nbInscrits = $sortie->getParticipants()->count();
        if ($nbInscrits >= $sortie->getNbInscriptionsMax()) {
            return [
                'success' => false,
                'message' => 'Il n\'y a plus de places disponibles pour cette sortie.'
            ];
        }

        // Vérifier que le participant n'est pas déjà inscrit
        if ($sortie->getParticipants()->contains($participant)) {
            return [
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à cette sortie.'
            ];
        }

        // Vérifier que l'organisateur ne s'inscrit pas à sa propre sortie
        if ($sortie->getOrganisateur() === $participant) {
            return [
                'success' => false,
                'message' => 'Vous êtes l\'organisateur de cette sortie, vous ne pouvez pas vous y inscrire.'
            ];
        }

        // Inscription du participant
        $sortie->addParticipant($participant);
        $participant->addSortieParticipee($sortie);

        $this->entityManager->persist($sortie);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Inscription réussie !'
        ];
    }

    /**
     * Désinscrit un participant d'une sortie
     * 
     * @param Sortie $sortie La sortie de laquelle se désinscrire
     * @param Participant $participant Le participant qui se désinscrit
     * @return array ['success' => bool, 'message' => string]
     */
    public function desinscrireParticipant(Sortie $sortie, Participant $participant): array
    {
        // Vérifier que la sortie est dans l'état "Ouverte"
        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return [
                'success' => false,
                'message' => 'Cette sortie n\'est pas ouverte, vous ne pouvez pas vous désinscrire.'
            ];
        }

        // Vérifier que la date limite d'inscription n'est pas dépassée
        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            return [
                'success' => false,
                'message' => 'La date limite d\'inscription est dépassée, vous ne pouvez plus vous désinscrire.'
            ];
        }

        // Vérifier que le participant est bien inscrit
        if (!$sortie->getParticipants()->contains($participant)) {
            return [
                'success' => false,
                'message' => 'Vous n\'êtes pas inscrit à cette sortie.'
            ];
        }

        // Désinscription du participant
        $sortie->removeParticipant($participant);
        $participant->removeSortieParticipee($sortie);

        $this->entityManager->persist($sortie);
        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Désinscription réussie !'
        ];
    }

    /**
     * Vérifie si un participant peut s'inscrire à une sortie
     * 
     * @param Sortie $sortie
     * @param Participant $participant
     * @return bool
     */
    public function peutSinscrire(Sortie $sortie, Participant $participant): bool
    {
        // Vérifier l'état
        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return false;
        }

        // Vérifier la date limite
        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            return false;
        }

        // Vérifier les places
        $nbInscrits = $sortie->getParticipants()->count();
        if ($nbInscrits >= $sortie->getNbInscriptionsMax()) {
            return false;
        }

        // Vérifier que pas déjà inscrit
        if ($sortie->getParticipants()->contains($participant)) {
            return false;
        }

        // Vérifier que ce n'est pas l'organisateur
        if ($sortie->getOrganisateur() === $participant) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si un participant peut se désinscrire d'une sortie
     * 
     * @param Sortie $sortie
     * @param Participant $participant
     * @return bool
     */
    public function peutSeDesinscrire(Sortie $sortie, Participant $participant): bool
    {
        // Vérifier l'état
        if (!$sortie->getEtat() || $sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return false;
        }

        // Vérifier la date limite
        $now = new \DateTimeImmutable();
        if ($sortie->getDateLimiteInscription() < $now) {
            return false;
        }

        // Vérifier que le participant est inscrit
        if (!$sortie->getParticipants()->contains($participant)) {
            return false;
        }

        return true;
    }
}

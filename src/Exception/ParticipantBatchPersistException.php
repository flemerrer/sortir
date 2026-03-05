<?php

    namespace App\Exception;

    class ParticipantBatchPersistException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de l'enregistrement des utilisateurs.";
    }

<?php

    namespace App\Exception;
    class ParticipantCreateException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la création du participant.";
    }

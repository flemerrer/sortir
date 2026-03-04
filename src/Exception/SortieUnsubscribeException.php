<?php

    namespace App\Service;

    class SortieUnsubscribeException extends \Exception
    {
        protected $message =  "Une erreur est survenue lors de la désinscription de l'utilisateur.";
    }
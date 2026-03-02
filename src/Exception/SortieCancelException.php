<?php

    namespace App\Exception;

    class SortieCancelException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de l'annulation de la sortie.";
    }
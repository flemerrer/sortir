<?php

    namespace App\Exception;

    class SortieUpdateException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la mise à jour de la sortie.";
    }

<?php

    namespace App\Exception;

    class CsvUploadException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la persistance de l'événement d'upload csv.";
    }
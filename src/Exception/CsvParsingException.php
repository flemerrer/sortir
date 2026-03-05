<?php

    namespace App\Exception;

    class CsvParsingException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la lecture du fichier csv.";
    }
<?php

    namespace App\Exception;

    class CsvBadFormatException extends \Exception
    {
        protected $message = "Le fichier csv n'est pas au bon format.";
    }
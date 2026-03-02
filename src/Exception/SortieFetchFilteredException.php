<?php

    namespace App\Exception;

    class SortieFetchFilteredException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la récupération des sorties.";
    }
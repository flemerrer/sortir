<?php

    namespace App\Exception;

    class SiteFetchException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la récupération des sites.";
    }

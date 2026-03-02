<?php

    namespace App\Exception;

    class SortieDeleteException extends \Exception
    {
        protected $message = 'Une erreur est survenue lors de la suppression de la sortie.';
    }

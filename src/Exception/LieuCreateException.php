<?php

    namespace App\Exception;

    class LieuCreateException extends \Exception
    {
        protected $message = "Une erreur est survenue lors de la création du lieu.";
    }

<?php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    /**
     * Contrôleur de test
     */
    class TestController extends AbstractController
    {

        #[Route('/dev/health_check', name: 'health_check')]
        public function login(): Response
        {
            return new Response('"App running."');
        }

        #[Route('/dev/error', name: 'app_error')]
        public function test(): Response
        {
            throw new \LogicException("Look for me in var/logs/errors.log");
        }
    }

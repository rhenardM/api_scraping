<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['GET', 'POST'])]
    public function ApiLogin(){

        $user = $this->getUser();

        $userData = [
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(), 
            'lastName' => $user->getLastName()
        ];

        return new JsonResponse($userData);
    }
}

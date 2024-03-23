<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods:['POST'])]
    public function index(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {

        $data = json_decode($request->getContent(), true);

        if(!isset($data['username']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = new User($data['username'], $data['password']);

        $token = $JWTManager->create($user); 

        return $this->json([
            'token' => $token,
        ]);
    }
}

// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\Security\Core\Exception\BadCredentialsException;
// use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
// use Symfony\Component\Security\Core\User\UserInterface;

// class AuthController extends AbstractController
// {
//     public function login(Request $request, JWTTokenManagerInterface $JWTManager)
//     {
//         $credentials = json_decode($request->getContent(), true);

//         if (empty($credentials['username'] || empty($credentials['password']))) {
//             throw new BadCredentialsException('Les informations d\'identification sont invalides. ');
//         }
        
//         $user = $this->getUserFromDatabase($credentials['username'], $credentials['password']);

//     }
// }
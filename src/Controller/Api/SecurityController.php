<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Authentication')]
class SecurityController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login_check',
        operationId: 'login',
        summary: 'Connexion pour obtenir un token JWT',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(
                        property: 'username',
                        type: 'string',
                        example: 'admin@simracing.com',
                        description: 'Email de l\'utilisateur'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'admin',
                        description: 'Mot de passe'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'
                        )
                    ]
                )
            )
        ]
    )]
    public function login(): void
    {
        // Cette méthode ne sera jamais exécutée
    }
} 
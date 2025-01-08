<?php

namespace App\Controller\Api\Admin;

use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Tag(name="Admin")
 */
#[Route('/api/admin', name: 'api_admin_')]
class TestController extends AbstractController
{
    /**
     * Test de connexion admin
     * 
     * @OA\Get(
     *     path="/api/admin/test",
     *     summary="Test de connexion admin",
     *     operationId="testAdmin"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Retourne un message de succès si l'utilisateur est admin",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message", type="string"),
     *        @OA\Property(property="user", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Non autorisé"
     * )
     * @Security(name="Bearer")
     */
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json([
            'message' => 'Connexion admin réussie',
            'user' => $this->getUser()->getUserIdentifier(),
        ]);
    }
} 
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
     *     operationId="testAdmin",
     *     tags={"Admin"},
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Connexion admin réussie"),
     *             @OA\Property(property="user", type="string", example="admin@simracing.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="JWT Token not found")
     *         )
     *     )
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
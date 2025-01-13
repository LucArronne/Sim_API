<?php

namespace App\Controller;

use App\Entity\Avis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(): Response
    {
        return $this->render('avis/index.html.twig');
    }

    #[Route('/api/avis', name: 'get_avis', methods: ['GET'])]
    #[OA\Get(
        path: '/api/avis',
        summary: 'Récupérer tous les avis',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des avis',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Avis')
                )
            )
        ]
    )]
    public function getAvis(EntityManagerInterface $entityManager): Response
    {
        $avis = $entityManager->getRepository(Avis::class)->findBy([], ['createdAt' => 'DESC']);
        
        return $this->json([
            'success' => true,
            'avis' => $avis
        ], 200, [], ['groups' => 'avis:read']);
    }

    #[Route('/api/avis', name: 'create_avis', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/api/avis',
        summary: 'Créer un nouvel avis',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['note', 'commentaire'],
                properties: [
                    new OA\Property(property: 'note', type: 'integer', minimum: 1, maximum: 5),
                    new OA\Property(property: 'commentaire', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Avis créé'),
            new OA\Response(response: 400, description: 'Données invalides')
        ]
    )]
    public function createAvis(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['note']) || !isset($data['commentaire'])) {
                throw new \Exception('Note et commentaire requis');
            }

            if ($data['note'] < 1 || $data['note'] > 5) {
                throw new \Exception('La note doit être entre 1 et 5');
            }

            $avis = new Avis();
            $avis->setUser($this->getUser());
            $avis->setNote($data['note']);
            $avis->setCommentaire($data['commentaire']);

            $entityManager->persist($avis);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Avis créé avec succès'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/avis/{id}', name: 'delete_avis', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteAvis(Avis $avis, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($avis);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Avis supprimé avec succès'
        ]);
    }

    #[Route('/api/avis/{id}/validate', name: 'validate_avis', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validateAvis(Avis $avis, EntityManagerInterface $entityManager): Response
    {
        $avis->setIsValid(true);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Avis validé avec succès'
        ]);
    }

    #[Route('/api/avis/valides', name: 'get_avis_valides', methods: ['GET'])]
    public function getAvisValides(EntityManagerInterface $entityManager): Response
    {
        $avis = $entityManager->getRepository(Avis::class)->findBy(
            ['isValid' => true],
            ['createdAt' => 'DESC']
        );
        
        return $this->json([
            'success' => true,
            'avis' => $avis
        ], 200, [], ['groups' => 'avis:read']);
    }
} 
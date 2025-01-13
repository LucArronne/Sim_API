<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reservations')]
class ReservationController extends AbstractController
{
    #[Route('/api/disponibilites', name: 'get_disponibilites', methods: ['GET'])]
    #[OA\Get(
        path: '/api/disponibilites',
        summary: 'Récupérer toutes les disponibilités',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des disponibilités',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Disponibilite')
                )
            )
        ]
    )]
    public function getDisponibilites(EntityManagerInterface $entityManager): Response
    {
        $disponibilites = $entityManager->getRepository(Disponibilite::class)->findBy(['isAvailable' => true]);
        
        return $this->json([
            'success' => true,
            'disponibilites' => array_map(function($dispo) {
                return [
                    'id' => $dispo->getId(),
                    'dateDebut' => $dispo->getDateDebut()->format('Y-m-d H:i:s'),
                    'dateFin' => $dispo->getDateFin()->format('Y-m-d H:i:s'),
                    'title' => $dispo->getTitle(),
                    'description' => $dispo->getDescription()
                ];
            }, $disponibilites)
        ]);
    }

    #[Route('/api/reservations', name: 'create_reservation', methods: ['POST'])]
    #[OA\Post(
        path: '/api/reservations',
        summary: 'Créer une nouvelle réservation',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['disponibiliteId'],
                properties: [
                    new OA\Property(property: 'disponibiliteId', type: 'integer', example: 1),
                    new OA\Property(property: 'notes', type: 'string', example: 'Notes spéciales pour la réservation')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Réservation créée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Réservation créée avec succès')
                    ]
                )
            )
        ]
    )]
    public function createReservation(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();
            
            $disponibilite = $entityManager->getRepository(Disponibilite::class)->find($data['disponibiliteId']);
            if (!$disponibilite || !$disponibilite->isIsAvailable()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Disponibilité non trouvée ou non disponible'
                ], Response::HTTP_BAD_REQUEST);
            }

            $reservation = new Reservation();
            $reservation->setUser($user);
            $reservation->setDisponibilite($disponibilite);
            if (isset($data['notes'])) {
                $reservation->setNotes($data['notes']);
            }

            $disponibilite->setIsAvailable(false);
            
            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Réservation créée avec succès'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/reservations', name: 'get_user_reservations', methods: ['GET'])]
    #[OA\Get(
        path: '/api/reservations',
        summary: 'Récupérer les réservations de l\'utilisateur connecté',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des réservations',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Reservation')
                )
            )
        ]
    )]
    public function getUserReservations(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(['user' => $user]);
        
        return $this->json([
            'success' => true,
            'reservations' => array_map(function($reservation) {
                return [
                    'id' => $reservation->getId(),
                    'status' => $reservation->getStatus(),
                    'createdAt' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'notes' => $reservation->getNotes(),
                    'disponibilite' => [
                        'dateDebut' => $reservation->getDisponibilite()->getDateDebut()->format('Y-m-d H:i:s'),
                        'dateFin' => $reservation->getDisponibilite()->getDateFin()->format('Y-m-d H:i:s'),
                        'title' => $reservation->getDisponibilite()->getTitle()
                    ]
                ];
            }, $reservations)
        ]);
    }

    #[Route('/reservation', name: 'app_reservation', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('reservation/index.html.twig');
    }
} 
<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[IsGranted('ROLE_ADMIN')]
class ReservationAdminController extends AbstractController
{
    #[Route('/admin/reservations', name: 'admin_reservations', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/reservations/index.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/admin/reservations/{id}/status', name: 'admin_reservation_status', methods: ['POST'])]
    public function updateStatus(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $newStatus = $data['status'] ?? null;

            if (!in_array($newStatus, ['pending', 'confirmed', 'cancelled'])) {
                throw new \Exception('Statut invalide');
            }

            $reservation->setStatus($newStatus);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/admin/reservations/{id}/delete', name: 'admin_reservation_delete', methods: ['DELETE'])]
    public function delete(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        try {
            $entityManager->remove($reservation);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Réservation supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/admin/disponibilites/create', name: 'admin_create_disponibilite', methods: ['POST'])]
    public function createDisponibilite(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $disponibilite = new Disponibilite();
            $disponibilite->setDateDebut(new \DateTime($data['dateDebut']));
            $disponibilite->setDateFin(new \DateTime($data['dateFin']));
            $disponibilite->setTitle($data['title'] ?? 'Créneau de simulation');
            $disponibilite->setDescription($data['description'] ?? null);
            $disponibilite->setIsAvailable(true);

            $entityManager->persist($disponibilite);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Disponibilité créée avec succès'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/admin/disponibilites', name: 'admin_disponibilites', methods: ['GET'])]
    public function disponibilites(EntityManagerInterface $entityManager): Response
    {
        $disponibilites = $entityManager->getRepository(Disponibilite::class)->findBy(
            [],
            ['dateDebut' => 'ASC']
        );

        return $this->render('admin/reservations/disponibilites.html.twig', [
            'disponibilites' => $disponibilites
        ]);
    }

    #[Route('/api/admin/reservations/{id}/validate', name: 'admin_validate_reservation', methods: ['PATCH'])]
    #[OA\Tag(name: 'Reservations')]
    #[OA\Patch(
        path: '/api/admin/reservations/{id}/validate',
        summary: 'Valider une réservation',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Réservation validée avec succès'
            )
        ]
    )]
    public function validateReservation(
        Reservation $reservation, 
        EntityManagerInterface $entityManager
    ): Response
    {
        try {
            $reservation->setStatus('validee');
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Réservation validée avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de la réservation'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/admin/reservations/pending', name: 'admin_get_pending_reservations', methods: ['GET'])]
    #[OA\Tag(name: 'Reservations')]
    #[OA\Get(
        path: '/api/admin/reservations/pending',
        summary: 'Récupérer les réservations en attente',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des réservations en attente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'reservations',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(
                                        property: 'user',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'email', type: 'string'),
                                            new OA\Property(property: 'firstName', type: 'string'),
                                            new OA\Property(property: 'lastName', type: 'string')
                                        ]
                                    ),
                                    new OA\Property(
                                        property: 'disponibilite',
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'dateDebut', type: 'string', format: 'datetime'),
                                            new OA\Property(property: 'dateFin', type: 'string', format: 'datetime'),
                                            new OA\Property(property: 'title', type: 'string')
                                        ]
                                    )
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function getPendingReservations(EntityManagerInterface $entityManager): Response
    {
        try {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(
                ['status' => 'en_attente'],
                ['createdAt' => 'DESC']
            );

            $formattedReservations = [];
            foreach ($reservations as $reservation) {
                $formattedReservations[] = [
                    'id' => $reservation->getId(),
                    'user' => [
                        'email' => $reservation->getUser()->getEmail(),
                        'firstName' => $reservation->getUser()->getFirstName(),
                        'lastName' => $reservation->getUser()->getLastName()
                    ],
                    'disponibilite' => [
                        'dateDebut' => $reservation->getDisponibilite()->getDateDebut()->format('Y-m-d H:i:s'),
                        'dateFin' => $reservation->getDisponibilite()->getDateFin()->format('Y-m-d H:i:s'),
                        'title' => $reservation->getDisponibilite()->getTitle()
                    ],
                    'createdAt' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'notes' => $reservation->getNotes()
                ];
            }

            return $this->json([
                'success' => true,
                'reservations' => $formattedReservations
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des réservations en attente'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
} 
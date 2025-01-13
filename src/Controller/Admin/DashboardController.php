<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $token = $request->request->get('_token');
        if ($token) {
            // Le token est présent dans la requête POST
            return $this->render('admin/dashboard.html.twig', [
                'token' => $token
            ]);
        }

        return $this->render('admin/dashboard.html.twig');
    }
} 
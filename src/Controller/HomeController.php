<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('pages/home.html.twig', [
            'services' => [
                [
                    'title' => 'Découpe carbone',
                    'description' => 'Service de découpe de carbone pour la personnalisation de vos équipements.',
                    'image' => 'images/services/decoupe-carbone.jpg',
                    'button' => 'Devis gratuit'
                ],
                [
                    'title' => 'Gestion de simracing',
                    'description' => 'Bénéficiez nos conseils de spécialistes dans vos configurations.',
                    'image' => 'images/services/gestion-simracing.jpg',
                    'button' => 'Réservation'
                ],
                [
                    'title' => 'Impression 3D',
                    'description' => 'Service d\'impression 3D et prototypage de vos pièces sur-mesure.',
                    'image' => 'images/services/impression-3d.jpg',
                    'button' => 'Devis gratuit'
                ],
            ]
        ]);
    }
} 
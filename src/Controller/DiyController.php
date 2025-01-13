<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiyController extends AbstractController
{
    #[Route('/diy', name: 'app_diy')]
    public function index(): Response
    {
        return $this->render('diy/index.html.twig');
    }
} 
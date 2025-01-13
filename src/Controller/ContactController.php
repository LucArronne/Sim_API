<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Contact')]
class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'contact_api', methods: ['POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $contact = new Contact();
            $form = $this->createForm(ContactType::class, $contact);
            
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                throw new \Exception('Invalid JSON data');
            }

            $form->submit($data);

            if ($form->isValid()) {
                $entityManager->persist($contact);
                $entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Votre message a été envoyé avec succès'
                ], Response::HTTP_CREATED);
            }

            return $this->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $this->getFormErrors($form)
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact', name: 'contact_page', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
} 
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
    #[Route('/contact', name: 'contact_page', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

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

    #[Route('/api/admin/contacts', name: 'get_contacts', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/contacts',
        summary: 'Récupérer tous les messages de contact',
        tags: ['Contact'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des messages récupérée avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'contacts',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Contact')
                        )
                    ]
                )
            )
        ]
    )]
    public function getContacts(EntityManagerInterface $entityManager): Response
    {
        $contacts = $entityManager->getRepository(Contact::class)->findBy([], ['createdAt' => 'DESC']);
        
        return $this->json([
            'success' => true,
            'contacts' => array_map(function($contact) {
                return [
                    'id' => $contact->getId(),
                    'email' => $contact->getEmail(),
                    'name' => $contact->getName(),
                    'subject' => $contact->getSubject(),
                    'message' => $contact->getMessage(),
                    'createdAt' => $contact->getCreatedAt()->format('Y-m-d H:i:s'),
                    'isRead' => $contact->isRead()
                ];
            }, $contacts)
        ]);
    }

    #[Route('/api/admin/contacts/{id}', name: 'delete_contact', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/admin/contacts/{id}',
        summary: 'Supprimer un message de contact',
        tags: ['Contact'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du message à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Message supprimé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Message supprimé avec succès')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Message non trouvé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Message non trouvé')
                    ]
                )
            )
        ]
    )]
    public function deleteContact(int $id, EntityManagerInterface $entityManager): Response
    {
        $contact = $entityManager->getRepository(Contact::class)->find($id);
        
        if (!$contact) {
            return $this->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($contact);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Message supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
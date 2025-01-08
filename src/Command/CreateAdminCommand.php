<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Création du compte administrateur unique'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Cette commande permet de créer le compte administrateur unique du système');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Vérifier si un admin existe déjà
            $existingAdmin = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['roles' => ['ROLE_ADMIN']]);

            // Si un admin existe déjà, on affiche ses informations
            if ($existingAdmin) {
                $io->warning('Un administrateur existe déjà !');
                $io->table(
                    ['Email', 'Prénom', 'Nom'],
                    [[
                        $existingAdmin->getEmail(),
                        $existingAdmin->getFirstName(),
                        $existingAdmin->getLastName()
                    ]]
                );
                return Command::FAILURE;
            }

            // Supprimer tous les utilisateurs avec ROLE_ADMIN (par sécurité)
            $qb = $this->entityManager->createQueryBuilder();
            $qb->delete(User::class, 'u')
               ->where('u.roles LIKE :role')
               ->setParameter('role', '%ROLE_ADMIN%')
               ->getQuery()
               ->execute();

            // Créer l'admin unique
            $admin = new User();
            $admin->setEmail('admin@simracing.com');
            $admin->setFirstName('Admin');
            $admin->setLastName('Simracing');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setCreatedAt(new \DateTimeImmutable());

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin');
            $admin->setPassword($hashedPassword);

            // Sauvegarder en base de données
            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $io->success('Administrateur créé avec succès !');
            $io->table(
                ['Email', 'Mot de passe', 'Rôle'],
                [['admin@simracing.com', 'admin', 'ROLE_ADMIN']]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Une erreur est survenue : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
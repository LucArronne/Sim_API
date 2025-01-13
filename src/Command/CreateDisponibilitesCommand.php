<?php

namespace App\Command;

use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-disponibilites',
    description: 'Crée des disponibilités de test'
)]
class CreateDisponibilitesCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Cette commande crée 5 disponibilités pour les prochains jours');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Créer quelques disponibilités pour les prochains jours
            for ($i = 1; $i <= 5; $i++) {
                $disponibilite = new Disponibilite();
                
                $dateDebut = new \DateTime("+$i days");
                $dateDebut->setTime(10, 0); // 10h00
                
                $dateFin = clone $dateDebut;
                $dateFin->modify('+2 hours'); // Créneau de 2 heures
                
                $disponibilite->setDateDebut($dateDebut);
                $disponibilite->setDateFin($dateFin);
                $disponibilite->setTitle("Créneau de simulation $i");
                $disponibilite->setDescription("Session de simulation de 2 heures");
                $disponibilite->setIsAvailable(true);
                
                $this->entityManager->persist($disponibilite);
            }

            $this->entityManager->flush();

            $io->success('5 disponibilités ont été créées avec succès.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Une erreur est survenue : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionXXXXXXXX extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Ajoutez uniquement la colonne is_valid à la table avis existante
        $this->addSql('ALTER TABLE avis ADD is_valid TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis DROP is_valid');
    }
} 
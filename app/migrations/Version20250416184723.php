<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416184723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modification du type de la colonne validation_type dans la table character';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` ADD validation_type SMALLINT(6) NOT NULL');
        $this->addSql('ALTER TABLE `character` DROP COLUMN is_validated');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` ADD is_validated TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('UPDATE `character` SET is_validated = CASE WHEN validation_type > 0 THEN 1 ELSE 0 END');
        $this->addSql('ALTER TABLE `character` DROP validation_type');
    }
}

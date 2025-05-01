<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501220926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ locked aux tables skill_learned, possession et character_asset';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill_learned ADD locked BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE possession ADD locked BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE character_asset ADD locked BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE skill_learned DROP locked');
        $this->addSql('ALTER TABLE possession DROP locked');
        $this->addSql('ALTER TABLE character_asset DROP locked');
    }
}

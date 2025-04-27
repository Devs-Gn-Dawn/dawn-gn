<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427205409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create asset and character_asset tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset (
            id INT AUTO_INCREMENT NOT NULL,
            type ENUM(\'Object\', \'Capacity\', \'Skill\', \'Gear\') NOT NULL,
            is_catalog TINYINT(1) NOT NULL,
            label VARCHAR(255) NOT NULL,
            base_cost SMALLINT NOT NULL,
            description LONGTEXT NOT NULL,
            short LONGTEXT NOT NULL,
            quote LONGTEXT NOT NULL,
            required_classes TEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
            required_factions TEXT NOT NULL COMMENT \'(DC2Type:simple_array)\',
            required_skill_id INT DEFAULT NULL,
            visibility TINYINT(1) NOT NULL,
            base_note LONGTEXT DEFAULT NULL,
            base_note_orga LONGTEXT DEFAULT NULL,
            rarity ENUM(\'Abondant\', \'Commun\', \'Peu Commun\', \'Rare\', \'Très Rare\', \'Extrêmement Rare\', \'Unique\', \'Secret\') NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE character_asset (
            id INT AUTO_INCREMENT NOT NULL,
            quantity INT NOT NULL,
            cost SMALLINT NOT NULL,
            note LONGTEXT DEFAULT NULL,
            note_orga LONGTEXT DEFAULT NULL,
            fk_character INT NOT NULL,
            fk_asset INT NOT NULL,
            aquired_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE character_asset ADD CONSTRAINT FK_CHARACTER_ASSET_CHARACTER FOREIGN KEY (fk_character) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_asset ADD CONSTRAINT FK_CHARACTER_ASSET_ASSET FOREIGN KEY (fk_asset) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_ASSET_REQUIRED_SKILL FOREIGN KEY (required_skill_id) REFERENCES skill (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE character_asset DROP FOREIGN KEY FK_CHARACTER_ASSET_CHARACTER');
        $this->addSql('ALTER TABLE character_asset DROP FOREIGN KEY FK_CHARACTER_ASSET_ASSET');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_ASSET_REQUIRED_SKILL');
        $this->addSql('DROP TABLE character_asset');
        $this->addSql('DROP TABLE asset');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use App\Entity\CharacterType;

final class Version20250407000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remplace is_main par type dans la table character';
    }

    public function up(Schema $schema): void
    {
        // Ajout de la nouvelle colonne type
        $this->addSql('ALTER TABLE `character` ADD type ENUM(\'Main\', \'Secondary\', \'Draft\') NOT NULL DEFAULT \'Main\'');

        // Migration des données existantes
        $this->addSql('UPDATE `character` SET type = CASE WHEN is_main = 1 THEN \'Main\' ELSE \'Draft\' END');

        // Suppression de l'ancienne colonne
        $this->addSql('ALTER TABLE `character` DROP COLUMN is_main');
    }

    public function down(Schema $schema): void
    {
        // Ajout de l'ancienne colonne is_main
        $this->addSql('ALTER TABLE `character` ADD is_main TINYINT(1) NOT NULL DEFAULT 0');

        // Migration des données en arrière
        $this->addSql('UPDATE `character` SET is_main = CASE WHEN type = \'Main\' THEN 1 ELSE 0 END');

        // Suppression de la nouvelle colonne
        $this->addSql('ALTER TABLE `character` DROP COLUMN type');
    }
}

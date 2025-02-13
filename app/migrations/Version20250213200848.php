<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213200848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, fk_user INT NOT NULL, name VARCHAR(127) NOT NULL, description LONGTEXT NOT NULL, background LONGTEXT NOT NULL, class VARCHAR(31) NOT NULL, faction VARCHAR(31) NOT NULL, note_orga LONGTEXT NOT NULL, INDEX IDX_937AB0341AD0877 (fk_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gear (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(31) NOT NULL, base_cost SMALLINT NOT NULL, description LONGTEXT NOT NULL, short LONGTEXT NOT NULL, visibility TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE possession (id INT AUTO_INCREMENT NOT NULL, fk_character INT NOT NULL, fk_gear INT NOT NULL, cost SMALLINT NOT NULL, note LONGTEXT NOT NULL, note_orga LONGTEXT NOT NULL, INDEX IDX_F9EE3F42F22367A4 (fk_character), INDEX IDX_F9EE3F42877A8DA5 (fk_gear), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration (id INT AUTO_INCREMENT NOT NULL, fk_user INT NOT NULL, event VARCHAR(31) NOT NULL, helloasso_ticket VARCHAR(127) NOT NULL, INDEX IDX_62A8A7A71AD0877 (fk_user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(31) NOT NULL, base_cost SMALLINT NOT NULL, short LONGTEXT NOT NULL, description LONGTEXT NOT NULL, required_classes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', required_factions LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', visibility TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill_requirements (skill_id INT NOT NULL, required_skill_id INT NOT NULL, INDEX IDX_E2D231A85585C142 (skill_id), INDEX IDX_E2D231A8CEC0E2D5 (required_skill_id), PRIMARY KEY(skill_id, required_skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill_learned (id INT AUTO_INCREMENT NOT NULL, fk_character INT NOT NULL, fk_skill INT NOT NULL, cost SMALLINT NOT NULL, note LONGTEXT NOT NULL, note_orga LONGTEXT NOT NULL, INDEX IDX_E6CE134CF22367A4 (fk_character), INDEX IDX_E6CE134C9FD0C702 (fk_skill), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(127) NOT NULL, email VARCHAR(127) NOT NULL, role VARCHAR(31) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB0341AD0877 FOREIGN KEY (fk_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE possession ADD CONSTRAINT FK_F9EE3F42F22367A4 FOREIGN KEY (fk_character) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE possession ADD CONSTRAINT FK_F9EE3F42877A8DA5 FOREIGN KEY (fk_gear) REFERENCES gear (id)');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A71AD0877 FOREIGN KEY (fk_user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE skill_requirements ADD CONSTRAINT FK_E2D231A85585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE skill_requirements ADD CONSTRAINT FK_E2D231A8CEC0E2D5 FOREIGN KEY (required_skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE skill_learned ADD CONSTRAINT FK_E6CE134CF22367A4 FOREIGN KEY (fk_character) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE skill_learned ADD CONSTRAINT FK_E6CE134C9FD0C702 FOREIGN KEY (fk_skill) REFERENCES skill (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB0341AD0877');
        $this->addSql('ALTER TABLE possession DROP FOREIGN KEY FK_F9EE3F42F22367A4');
        $this->addSql('ALTER TABLE possession DROP FOREIGN KEY FK_F9EE3F42877A8DA5');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A71AD0877');
        $this->addSql('ALTER TABLE skill_requirements DROP FOREIGN KEY FK_E2D231A85585C142');
        $this->addSql('ALTER TABLE skill_requirements DROP FOREIGN KEY FK_E2D231A8CEC0E2D5');
        $this->addSql('ALTER TABLE skill_learned DROP FOREIGN KEY FK_E6CE134CF22367A4');
        $this->addSql('ALTER TABLE skill_learned DROP FOREIGN KEY FK_E6CE134C9FD0C702');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE gear');
        $this->addSql('DROP TABLE possession');
        $this->addSql('DROP TABLE registration');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_requirements');
        $this->addSql('DROP TABLE skill_learned');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

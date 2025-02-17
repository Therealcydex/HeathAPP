<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216172547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, appointment_id INT DEFAULT NULL, medical_details VARCHAR(255) NOT NULL, doctor_notes VARCHAR(255) NOT NULL, INDEX IDX_1FBFB8D9E5B533F9 (appointment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9E5B533F9');
        $this->addSql('DROP TABLE prescription');
    }
}

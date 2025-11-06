<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106130521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE bookings (id SERIAL NOT NULL, client_id INT NOT NULL, house_id INT NOT NULL, status VARCHAR(255) NOT NULL, comment VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    $this->addSql('CREATE INDEX IDX_7A853C3519EB6921 ON bookings (client_id)');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_7A853C356BB74515 ON bookings (house_id)');
    $this->addSql('CREATE TABLE houses (id SERIAL NOT NULL, house_name VARCHAR(255) NOT NULL, price INT NOT NULL, sleeps INT NOT NULL, distance_to_sea INT NOT NULL, has_tv BOOLEAN NOT NULL, PRIMARY KEY(id))');
    $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, name VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(12) DEFAULT NULL, PRIMARY KEY(id))');
    $this->addSql('ALTER TABLE bookings ADD CONSTRAINT FK_7A853C3519EB6921 FOREIGN KEY (client_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    $this->addSql('ALTER TABLE bookings ADD CONSTRAINT FK_7A853C356BB74515 FOREIGN KEY (house_id) REFERENCES houses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
}

public function down(Schema $schema): void
{
    $this->addSql('CREATE SCHEMA public');
    $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C3519EB6921');
    $this->addSql('ALTER TABLE bookings DROP CONSTRAINT FK_7A853C356BB74515');
    $this->addSql('DROP TABLE bookings');
    $this->addSql('DROP TABLE houses');
    $this->addSql('DROP TABLE users');
}
}

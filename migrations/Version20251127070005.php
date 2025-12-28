<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127070005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD password VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL');
        $this->addSql('ALTER TABLE users ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE users ALTER phone_number SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E96B01BC5B ON users (phone_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1483A5E96B01BC5B');
        $this->addSql('ALTER TABLE users DROP password');
        $this->addSql('ALTER TABLE users DROP roles');
        $this->addSql('ALTER TABLE users ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE users ALTER phone_number DROP NOT NULL');
    }
}

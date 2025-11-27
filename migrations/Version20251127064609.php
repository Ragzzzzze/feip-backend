<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251127070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove duplicate tables user, booking, house';
    }

    public function up(Schema $schema): void
    {
        // Удаляем только дублирующиеся таблицы, оставляя правильные (users, bookings, houses)
        $this->addSql('DROP TABLE IF EXISTS "user" CASCADE');
        $this->addSql('DROP TABLE IF EXISTS booking CASCADE');
        $this->addSql('DROP TABLE IF EXISTS house CASCADE');
    }

    public function down(Schema $schema): void
    {
        // ВНИМАНИЕ: down миграция здесь не нужна, т.к. мы удаляем дубликаты
        // Не восстанавливаем удаленные таблицы
    }
}
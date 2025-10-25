<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for User entity with UUID, roles, and created_at
 */
final class Version20251025151808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table with UUID primary key, roles, and created_at fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id UUID NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            username VARCHAR(50) NOT NULL,
            roles JSON NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        $this->addSql('CREATE UNIQUE INDEX users_username_unique ON users (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}

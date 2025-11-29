<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Quest entity with UUID and all quest fields
 */
final class Version20251129120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create quests table with UUID primary key and all quest fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quests (
            id UUID NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            city VARCHAR(100),
            difficulty VARCHAR(50),
            duration_minutes INT,
            distance_km NUMERIC(10, 2),
            image_url VARCHAR(500),
            author VARCHAR(255),
            likes_count INT DEFAULT 0 NOT NULL,
            is_popular BOOLEAN DEFAULT FALSE NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        
        $this->addSql('COMMENT ON COLUMN quests.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quests.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN quests.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        // Индексы для оптимизации поиска
        $this->addSql('CREATE INDEX quests_city_idx ON quests (city)');
        $this->addSql('CREATE INDEX quests_difficulty_idx ON quests (difficulty)');
        $this->addSql('CREATE INDEX quests_is_popular_idx ON quests (is_popular)');
        $this->addSql('CREATE INDEX quests_created_at_idx ON quests (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE quests');
    }
}

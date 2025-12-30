<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228204039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create quest_likes table for dedicated likes storage';
    }

    public function up(Schema $schema): void
    {
        // Create quest_likes table
        $this->addSql('CREATE TABLE quest_likes (
            id UUID NOT NULL,
            user_id UUID NOT NULL,
            quest_id UUID NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT unique_user_quest_like UNIQUE (user_id, quest_id)
        )');
        
        // Add indices
        $this->addSql('CREATE INDEX idx_quest_likes_user ON quest_likes(user_id)');
        $this->addSql('CREATE INDEX idx_quest_likes_quest ON quest_likes(quest_id)');
        $this->addSql('CREATE INDEX idx_quest_likes_created_at ON quest_likes(created_at)');
        
        // Doctrine type hints
        $this->addSql('COMMENT ON COLUMN quest_likes.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quest_likes.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quest_likes.quest_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN quest_likes.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Drop quest_likes table
        $this->addSql('DROP TABLE quest_likes');
    }
}

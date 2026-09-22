<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229073420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove is_liked column from user_quest_progress (migrated to quest_likes table)';
    }

    public function up(Schema $schema): void
    {
        // Drop index first, then column
        $this->addSql('DROP INDEX IF EXISTS idx_user_liked');
        $this->addSql('ALTER TABLE user_quest_progress DROP COLUMN is_liked');
    }

    public function down(Schema $schema): void
    {
        // Restore is_liked column and index
        $this->addSql('ALTER TABLE user_quest_progress ADD COLUMN is_liked BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('CREATE INDEX idx_user_liked ON user_quest_progress (user_id, is_liked)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for UserQuestProgress table and Quest geolocation fields
 */
final class Version20251129152009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_quest_progress table and add lat/lng to quests table';
    }

    public function up(Schema $schema): void
    {
        // Fix quests table distance_km type and add geolocation fields
        $this->addSql('ALTER TABLE quests ALTER distance_km TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE quests ADD COLUMN latitude DOUBLE PRECISION NULL');
        $this->addSql('ALTER TABLE quests ADD COLUMN longitude DOUBLE PRECISION NULL');
        
        // Create geolocation index for quests table (only new index)
        $this->addSql('CREATE INDEX quests_geolocation_idx ON quests (latitude, longitude)');
        
        // Create user_quest_progress table
        $this->addSql('CREATE TABLE user_quest_progress (
            id UUID NOT NULL,
            user_id UUID NOT NULL,
            quest_id UUID NOT NULL,
            status VARCHAR(20) DEFAULT \'active\' NOT NULL,
            is_liked BOOLEAN DEFAULT FALSE NOT NULL,
            completed_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        
        $this->addSql('COMMENT ON COLUMN user_quest_progress.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_quest_progress.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_quest_progress.quest_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_quest_progress.completed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_quest_progress.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_quest_progress.updated_at IS \'(DC2Type:datetime_immutable)\'');
        
        // Add foreign key constraints
        $this->addSql('ALTER TABLE user_quest_progress ADD CONSTRAINT fk_user_quest_progress_user 
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_quest_progress ADD CONSTRAINT fk_user_quest_progress_quest 
            FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE');
        
        // Add unique constraint
        $this->addSql('ALTER TABLE user_quest_progress ADD CONSTRAINT unique_user_quest 
            UNIQUE (user_id, quest_id)');
        
        // Add check constraint for status
        $this->addSql('ALTER TABLE user_quest_progress ADD CONSTRAINT check_status 
            CHECK (status IN (\'active\', \'paused\', \'completed\'))');
        
        // Create indexes for user_quest_progress
        $this->addSql('CREATE INDEX idx_user_status ON user_quest_progress(user_id, status)');
        $this->addSql('CREATE INDEX idx_quest ON user_quest_progress(quest_id)');
        $this->addSql('CREATE INDEX idx_user_liked ON user_quest_progress(user_id, is_liked)');
    }

    public function down(Schema $schema): void
    {
        // Drop user_quest_progress table
        $this->addSql('DROP TABLE user_quest_progress');
        
        // Remove geolocation fields from quests
        $this->addSql('DROP INDEX quests_geolocation_idx');
        $this->addSql('ALTER TABLE quests DROP COLUMN latitude');
        $this->addSql('ALTER TABLE quests DROP COLUMN longitude');
        
        // Revert quests table changes
        $this->addSql('ALTER TABLE quests ALTER distance_km TYPE NUMERIC(10, 2)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Quest Steps Implementation:
 * 1. Create quest_steps table
 * 2. Add type column to quests table
 * 3. Add current_step_number to user_quest_progress table
 */
final class Version20260112100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quest_steps table, quests.type, and user_quest_progress.current_step_number';
    }

    public function up(Schema $schema): void
    {
        // Create quest_steps table
        $this->addSql('CREATE TABLE quest_steps (
            id SERIAL PRIMARY KEY,
            quest_id UUID NOT NULL,
            number INTEGER NOT NULL,
            title VARCHAR(255),
            text TEXT,
            image_url VARCHAR(500),
            audio_url VARCHAR(500),
            video_url VARCHAR(500),
            lat DOUBLE PRECISION NOT NULL,
            lng DOUBLE PRECISION NOT NULL,
            radius INTEGER NOT NULL,
            status INTEGER DEFAULT 1 NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            CONSTRAINT quest_steps_quest_number_unique UNIQUE (quest_id, number)
        )');
        
        // Add indexes for quest_steps
        $this->addSql('CREATE INDEX idx_quest_steps_quest_id ON quest_steps (quest_id)');
        $this->addSql('CREATE INDEX idx_quest_steps_status ON quest_steps (status)');
        
        // Add comment
        $this->addSql("COMMENT ON TABLE quest_steps IS 'Quest checkpoints/steps with geolocation validation'");
        
        // Add type column to quests
        $this->addSql("ALTER TABLE quests ADD type VARCHAR(20) DEFAULT 'linear' NOT NULL");
        
        // Add current_step_number to user_quest_progress
        $this->addSql('ALTER TABLE user_quest_progress ADD current_step_number INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Drop quest_steps table
        $this->addSql('DROP TABLE IF EXISTS quest_steps');
        
        // Remove type from quests
        $this->addSql('ALTER TABLE quests DROP COLUMN type');
        
        // Remove current_step_number from user_quest_progress
        $this->addSql('ALTER TABLE user_quest_progress DROP COLUMN current_step_number');
    }
}

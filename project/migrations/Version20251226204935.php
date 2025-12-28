<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226204935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание таблицы domain_events_progress для хранения доменных событий UserQuestProgress агрегата';
    }

    public function up(Schema $schema): void
    {
        // Создание таблицы domain_events_progress
        $this->addSql('
            CREATE TABLE domain_events_progress (
                aggregate_id UUID NOT NULL,
                user_id UUID NOT NULL,
                quest_id UUID NOT NULL,
                event_type VARCHAR(255) NOT NULL,
                event_data JSONB NOT NULL,
                platform JSONB NOT NULL,
                occurred_at TIMESTAMP DEFAULT NOW()
            )
        ');

        // Создание индексов для оптимизации запросов
        $this->addSql('CREATE INDEX idx_progress_events_aggregate ON domain_events_progress(aggregate_id)');
        $this->addSql('CREATE INDEX idx_progress_events_user ON domain_events_progress(user_id)');
        $this->addSql('CREATE INDEX idx_progress_events_quest ON domain_events_progress(quest_id)');
        $this->addSql('CREATE INDEX idx_progress_events_type ON domain_events_progress(event_type)');
        $this->addSql('CREATE INDEX idx_progress_events_occurred ON domain_events_progress(occurred_at)');
    }

    public function down(Schema $schema): void
    {
        // Удаление таблицы domain_events_progress
        $this->addSql('DROP TABLE IF EXISTS domain_events_progress');
    }
}

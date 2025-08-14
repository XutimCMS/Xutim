<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250814085105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add session bucket and optimize analytics event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xutim_analytics_event ADD session_bucket VARCHAR(128) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER path TYPE TEXT');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER referer TYPE TEXT');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER user_agent TYPE TEXT');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER screen_size TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER anonymized_ip TYPE VARCHAR(128)');
        $this->addSql('CREATE INDEX idx_event_recorded_at ON xutim_analytics_event (recorded_at)');
        $this->addSql('CREATE INDEX idx_event_path_time ON xutim_analytics_event (path, recorded_at)');
        $this->addSql('CREATE INDEX idx_event_session_time ON xutim_analytics_event (session_bucket, recorded_at)');
        $this->addSql('CREATE INDEX idx_event_isbot_time ON xutim_analytics_event (is_bot, recorded_at)');
        $this->addSql('CREATE INDEX idx_event_country_time ON xutim_analytics_event (country, recorded_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_event_recorded_at');
        $this->addSql('DROP INDEX idx_event_path_time');
        $this->addSql('DROP INDEX idx_event_session_time');
        $this->addSql('DROP INDEX idx_event_isbot_time');
        $this->addSql('DROP INDEX idx_event_country_time');
        $this->addSql('ALTER TABLE xutim_analytics_event DROP session_bucket');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER path TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER referer TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER user_agent TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER screen_size TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER anonymized_ip TYPE VARCHAR(255)');
    }
}

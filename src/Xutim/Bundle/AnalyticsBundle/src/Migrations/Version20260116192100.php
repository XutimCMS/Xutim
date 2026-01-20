<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260116192100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add analytics aggregation tables, archive table, and UTM fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xutim_analytics_event ADD utm_source VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE xutim_analytics_event ADD utm_medium VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE xutim_analytics_event ADD utm_campaign VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE xutim_analytics_event ALTER session_bucket DROP DEFAULT');

        $this->addSql('
            CREATE TABLE xutim_analytics_event_archive (
                id UUID NOT NULL,
                path TEXT NOT NULL,
                referer TEXT DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                language VARCHAR(255) DEFAULT NULL,
                screen_size VARCHAR(32) DEFAULT NULL,
                load_time_ms INT DEFAULT NULL,
                scroll_depth INT DEFAULT NULL,
                clicked_elements JSON DEFAULT NULL,
                country VARCHAR(2) DEFAULT NULL,
                is_bot BOOLEAN NOT NULL,
                recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                anonymized_ip VARCHAR(128) DEFAULT NULL,
                session_bucket VARCHAR(128) NOT NULL,
                utm_source VARCHAR(255) DEFAULT NULL,
                utm_medium VARCHAR(255) DEFAULT NULL,
                utm_campaign VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('CREATE INDEX idx_archive_recorded_at ON xutim_analytics_event_archive (recorded_at)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_summary (
                date DATE NOT NULL,
                path TEXT NOT NULL,
                pageviews INT NOT NULL,
                unique_visitors INT NOT NULL,
                avg_scroll_depth DOUBLE PRECISION DEFAULT NULL,
                avg_load_time DOUBLE PRECISION DEFAULT NULL,
                PRIMARY KEY(date, path)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_summary_date ON xutim_analytics_daily_summary (date)');
        $this->addSql('CREATE INDEX idx_daily_summary_path ON xutim_analytics_daily_summary (path)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_page_referrer (
                date DATE NOT NULL,
                target_path TEXT NOT NULL,
                referrer TEXT NOT NULL,
                is_external BOOLEAN NOT NULL,
                visits INT NOT NULL,
                unique_visitors INT NOT NULL,
                PRIMARY KEY(date, target_path, referrer, is_external)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_page_ref_date ON xutim_analytics_daily_page_referrer (date)');
        $this->addSql('CREATE INDEX idx_daily_page_ref_target ON xutim_analytics_daily_page_referrer (target_path)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_traffic_source (
                date DATE NOT NULL,
                source VARCHAR(255) NOT NULL,
                visits INT NOT NULL,
                unique_visitors INT NOT NULL,
                PRIMARY KEY(date, source)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_traffic_date ON xutim_analytics_daily_traffic_source (date)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_country (
                date DATE NOT NULL,
                country VARCHAR(2) NOT NULL,
                visits INT NOT NULL,
                unique_visitors INT NOT NULL,
                PRIMARY KEY(date, country)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_country_date ON xutim_analytics_daily_country (date)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_device (
                date DATE NOT NULL,
                device_type VARCHAR(32) NOT NULL,
                browser VARCHAR(64) NOT NULL,
                os VARCHAR(64) NOT NULL,
                visits INT NOT NULL,
                unique_visitors INT NOT NULL,
                PRIMARY KEY(date, device_type, browser, os)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_device_date ON xutim_analytics_daily_device (date)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_utm (
                date DATE NOT NULL,
                utm_source VARCHAR(255) NOT NULL,
                utm_medium VARCHAR(255) NOT NULL,
                utm_campaign VARCHAR(255) NOT NULL,
                visits INT NOT NULL,
                unique_visitors INT NOT NULL,
                PRIMARY KEY(date, utm_source, utm_medium, utm_campaign)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_utm_date ON xutim_analytics_daily_utm (date)');

        $this->addSql('
            CREATE TABLE xutim_analytics_daily_session (
                date DATE NOT NULL,
                entry_path TEXT NOT NULL,
                exit_path TEXT NOT NULL,
                session_count INT NOT NULL,
                total_pageviews INT NOT NULL,
                bounces INT NOT NULL,
                total_duration_seconds INT NOT NULL,
                PRIMARY KEY(date, entry_path, exit_path)
            )
        ');
        $this->addSql('CREATE INDEX idx_daily_session_date ON xutim_analytics_daily_session (date)');
        $this->addSql('CREATE INDEX idx_daily_session_entry ON xutim_analytics_daily_session (entry_path)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE xutim_analytics_daily_session');
        $this->addSql('DROP TABLE xutim_analytics_daily_utm');
        $this->addSql('DROP TABLE xutim_analytics_daily_device');
        $this->addSql('DROP TABLE xutim_analytics_daily_country');
        $this->addSql('DROP TABLE xutim_analytics_daily_traffic_source');
        $this->addSql('DROP TABLE xutim_analytics_daily_page_referrer');
        $this->addSql('DROP TABLE xutim_analytics_daily_summary');
        $this->addSql('DROP TABLE xutim_analytics_event_archive');

        $this->addSql('ALTER TABLE xutim_analytics_event ALTER session_bucket SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE xutim_analytics_event DROP utm_source');
        $this->addSql('ALTER TABLE xutim_analytics_event DROP utm_medium');
        $this->addSql('ALTER TABLE xutim_analytics_event DROP utm_campaign');
    }
}

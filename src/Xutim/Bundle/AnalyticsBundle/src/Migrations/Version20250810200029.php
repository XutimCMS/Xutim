<?php

declare(strict_types=1);

namespace Xutim\AnalyticsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250810200029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial xutim_analytics_event table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE xutim_analytics_event (id UUID NOT NULL, path VARCHAR(255) NOT NULL, referer VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, language VARCHAR(255) DEFAULT NULL, screen_size VARCHAR(255) DEFAULT NULL, load_time_ms INT DEFAULT NULL, scroll_depth INT DEFAULT NULL, clicked_elements JSON DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, is_bot BOOLEAN NOT NULL, recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, anonymized_ip VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE xutim_analytics_event');
    }
}

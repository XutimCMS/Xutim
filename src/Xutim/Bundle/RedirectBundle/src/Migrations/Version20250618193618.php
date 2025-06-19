<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250618193618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database structure';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE xutim_redirect (id UUID NOT NULL, source VARCHAR(255) NOT NULL, locale VARCHAR(255) DEFAULT NULL, permanent BOOLEAN NOT NULL, target_content_translation_id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C5F493099BE13D7 ON xutim_redirect (target_content_translation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE xutim_redirect ADD CONSTRAINT FK_8C5F493099BE13D7 FOREIGN KEY (target_content_translation_id) REFERENCES xutim_content_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE xutim_redirect DROP CONSTRAINT FK_8C5F493099BE13D7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE xutim_redirect
        SQL);
    }
}

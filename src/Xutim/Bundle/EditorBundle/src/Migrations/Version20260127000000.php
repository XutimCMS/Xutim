<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260127000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add content snapshot columns to content_draft table for draft mechanism';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE content_draft ADD pre_title VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE content_draft ADD title VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE content_draft ADD sub_title VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE content_draft ADD slug VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE content_draft ADD description TEXT NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE content_draft ADD content JSON NOT NULL DEFAULT '[]'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE content_draft DROP COLUMN pre_title');
        $this->addSql('ALTER TABLE content_draft DROP COLUMN title');
        $this->addSql('ALTER TABLE content_draft DROP COLUMN sub_title');
        $this->addSql('ALTER TABLE content_draft DROP COLUMN slug');
        $this->addSql('ALTER TABLE content_draft DROP COLUMN description');
        $this->addSql('ALTER TABLE content_draft DROP COLUMN content');
    }
}

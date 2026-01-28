<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260123000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create content_draft and content_block tables for EditorBundle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE content_draft (id UUID NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, translation_id UUID NOT NULL, user_id UUID DEFAULT NULL, based_on_draft_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ADBFC2469CAA2B25 ON content_draft (translation_id)');
        $this->addSql('CREATE INDEX IDX_ADBFC246A76ED395 ON content_draft (user_id)');
        $this->addSql('CREATE INDEX IDX_ADBFC24640DDCC64 ON content_draft (based_on_draft_id)');

        $this->addSql('CREATE TABLE content_block (id UUID NOT NULL, slot INT DEFAULT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, draft_id UUID NOT NULL, parent_id UUID DEFAULT NULL, type VARCHAR(50) NOT NULL, html TEXT DEFAULT NULL, attribution VARCHAR(255) DEFAULT NULL, level INT DEFAULT NULL, caption TEXT DEFAULT NULL, file_id UUID DEFAULT NULL, service VARCHAR(50) DEFAULT NULL, source TEXT DEFAULT NULL, code TEXT DEFAULT NULL, language VARCHAR(50) DEFAULT NULL, list_type VARCHAR(20) DEFAULT NULL, indent INT DEFAULT NULL, checked BOOLEAN DEFAULT NULL, template VARCHAR(100) DEFAULT NULL, settings JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68D8C3F0E2F3C5D1 ON content_block (draft_id)');
        $this->addSql('CREATE INDEX IDX_68D8C3F0727ACA70 ON content_block (parent_id)');
        $this->addSql('CREATE INDEX IDX_68D8C3F093CB796C ON content_block (file_id)');

        $this->addSql('ALTER TABLE content_draft ADD CONSTRAINT FK_ADBFC24640DDCC64 FOREIGN KEY (based_on_draft_id) REFERENCES content_draft (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F0E2F3C5D1 FOREIGN KEY (draft_id) REFERENCES content_draft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE content_block ADD CONSTRAINT FK_68D8C3F0727ACA70 FOREIGN KEY (parent_id) REFERENCES content_block (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE content_block DROP CONSTRAINT FK_68D8C3F0E2F3C5D1');
        $this->addSql('ALTER TABLE content_block DROP CONSTRAINT FK_68D8C3F0727ACA70');
        $this->addSql('ALTER TABLE content_draft DROP CONSTRAINT FK_ADBFC24640DDCC64');
        $this->addSql('DROP TABLE content_block');
        $this->addSql('DROP TABLE content_draft');
    }
}

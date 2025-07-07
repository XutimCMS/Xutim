<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250707111533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use a plain text target instead of content translation and remove locale.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xutim_redirect DROP CONSTRAINT fk_8c5f493099be13d7');
        $this->addSql('DROP INDEX idx_8c5f493099be13d7');
        $this->addSql('ALTER TABLE xutim_redirect ADD target VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE xutim_redirect ALTER COLUMN target DROP DEFAULT');
        $this->addSql('ALTER TABLE xutim_redirect DROP locale');
        $this->addSql('ALTER TABLE xutim_redirect DROP target_content_translation_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE xutim_redirect ADD locale VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE xutim_redirect ADD target_content_translation_id UUID NOT NULL');
        $this->addSql('ALTER TABLE xutim_redirect DROP target');
        $this->addSql('ALTER TABLE xutim_redirect ADD CONSTRAINT fk_8c5f493099be13d7 FOREIGN KEY (target_content_translation_id) REFERENCES xutim_content_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8c5f493099be13d7 ON xutim_redirect (target_content_translation_id)');
    }
}

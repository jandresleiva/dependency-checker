<?php

declare(strict_types=1);

namespace exercise\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210924014513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds file path to Repository';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE repository ADD filePath varchar(255)");
        $this->addSql("ALTER TABLE repository ADD CONSTRAINT unique_path UNIQUE (filePath)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE DROP COLUMN filePath");
        $this->addSql("ALTER TABLE repository DROP CONSTRAINT unique_path");
    }
}

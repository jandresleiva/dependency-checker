<?php

declare(strict_types=1);

namespace exercise\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922172548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds isDirty field to Repository';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE repository ADD isDirty tinyint");
        $this->addSql("ALTER TABLE repository ADD CONSTRAINT unique_name UNIQUE (name)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE repository DROP COLUMN isDirty");
        $this->addSql("ALTER TABLE repository DROP CONSTRAINT unique_name");
    }
}

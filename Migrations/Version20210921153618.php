<?php

declare(strict_types=1);

namespace exercise\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210921153618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE repository (
                            id int NOT NULL AUTO_INCREMENT, 
                            name varchar(255), 
                            CONSTRAINT repository_pk PRIMARY KEY (id)
                        )");

        $this->addSql("CREATE TABLE repository_dependency (
                            repository_source int NOT NULL,
                            repository_target int NOT NULL,
                            PRIMARY KEY (repository_source, repository_target)
                        )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE repository");
        $this->addSql("DROP TABLE repository_dependency");
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230823084221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create security_user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE security_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE security_user (
          id INT NOT NULL,
          email VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52825A88E7927C74 ON security_user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE security_user_id_seq CASCADE');
        $this->addSql('DROP TABLE security_user');
    }
}

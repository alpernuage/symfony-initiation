<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230827104828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE security_user ADD password VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE security_user DROP password');
    }
}

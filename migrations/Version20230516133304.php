<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230516133304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create home table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE home (
          id UUID NOT NULL,
          user_id UUID NOT NULL,
          address TEXT NOT NULL,
          city TEXT NOT NULL,
          zip_code TEXT NOT NULL,
          country VARCHAR(2) NOT NULL,
          currently_occupied BOOLEAN NOT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_71D60CD0A76ED395 ON home (user_id)');
        $this->addSql('COMMENT ON COLUMN home.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN home.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE
          home
        ADD
          CONSTRAINT FK_71D60CD0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home DROP CONSTRAINT FK_71D60CD0A76ED395');
        $this->addSql('DROP TABLE home');
    }
}

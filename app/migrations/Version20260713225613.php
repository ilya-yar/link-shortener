<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713225613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link ADD search_hash VARCHAR(32) NOT NULL AFTER original_url');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F160FFF011 ON link (link_hash)');
        $this->addSql('CREATE INDEX search_hash_idx ON link (search_hash) USING HASH'); // TODO MySql не умеет в HASH индексы
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_36AC99F160FFF011 ON link');
        $this->addSql('DROP INDEX search_hash_idx ON link');
        $this->addSql('ALTER TABLE link DROP search_hash');
    }
}

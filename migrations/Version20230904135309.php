<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230904135309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration for chat';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chat (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, contact_id VARCHAR(255) NOT NULL, participant_token VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, connection_token VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE chat_message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, chat_id INTEGER NOT NULL, json CLOB NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , CONSTRAINT FK_FAB3FC161A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FAB3FC161A9A7125 ON chat_message (chat_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_message');
    }
}

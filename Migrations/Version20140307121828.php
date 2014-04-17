<?php

namespace DoctrineORMModule\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140307121828 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE user_provider DROP INDEX UNIQ_7249979CA76ED395, ADD INDEX IDX_7249979CA76ED395 (user_id)");
        $this->addSql("ALTER TABLE user_provider DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE user_provider ADD id INT AUTO_INCREMENT NOT NULL, add PRIMARY KEY (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE user_provider DROP INDEX IDX_7249979CA76ED395, ADD UNIQUE INDEX UNIQ_7249979CA76ED395 (user_id)");
        $this->addSql("ALTER TABLE user_provider DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE user_provider DROP id");
        $this->addSql("ALTER TABLE user_provider ADD PRIMARY KEY (provider_id)");
    }
}

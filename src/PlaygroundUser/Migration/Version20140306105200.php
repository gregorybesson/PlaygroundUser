<?php 

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20140306105200 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        echo "Test";
    }

    public function down(Schema $schema)
    {

    }
}
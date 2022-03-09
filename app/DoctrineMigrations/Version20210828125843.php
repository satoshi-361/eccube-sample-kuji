<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210828125843 extends AbstractMigration
{
    const NAME = 'dtb_csv';

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::NAME)) {
            return;
        }

        $this->addSql("INSERT INTO dtb_csv (csv_type_id, creator_id, entity_name, field_name, reference_field_name, disp_name, sort_no, enabled, create_date, update_date, discriminator_type) VALUES (6, 1 , ?, 'id', null, '会員ID', 91, true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,'csv')", ['Eccube\\\\Entity\\\\Customer']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

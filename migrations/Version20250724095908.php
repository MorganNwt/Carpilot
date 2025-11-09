<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250724095908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE estimation CHANGE offer_price offer_price NUMERIC(10, 0) DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486F35F62F2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1B80E486F35F62F2 ON vehicle
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle DROP estimation_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE estimation CHANGE status status VARCHAR(50) NOT NULL, CHANGE offer_price offer_price NUMERIC(5, 0) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD estimation_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486F35F62F2 FOREIGN KEY (estimation_id) REFERENCES estimation (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B80E486F35F62F2 ON vehicle (estimation_id)
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250724091758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD estimation_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486F35F62F2 FOREIGN KEY (estimation_id) REFERENCES estimation (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1B80E486F35F62F2 ON vehicle (estimation_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
}

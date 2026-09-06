<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528155954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout agency_id sur vehicle/estimation, champs transaction, type/readAt notification';
    }

    public function up(Schema $schema): void
    {
        /*
        |--------------------------------------------------------------------------
        | Notification
        |--------------------------------------------------------------------------
        */

        if (!$schema->getTable('notification')->hasColumn('type')) {
            $this->addSql("
                ALTER TABLE notification
                ADD type VARCHAR(50) DEFAULT NULL
            ");
        }

        if (!$schema->getTable('notification')->hasColumn('read_at')) {
            $this->addSql("
                ALTER TABLE notification
                ADD read_at DATETIME DEFAULT NULL
                COMMENT '(DC2Type:datetime_immutable)'
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        if (!$schema->getTable('transaction')->hasColumn('seller_id')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD seller_id INT DEFAULT NULL
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('agency_id')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD agency_id INT DEFAULT NULL
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('status')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD status VARCHAR(50) DEFAULT NULL
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('signed_at')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD signed_at DATETIME DEFAULT NULL
                COMMENT '(DC2Type:datetime_immutable)'
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('created_at')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD created_at DATETIME DEFAULT NULL
                COMMENT '(DC2Type:datetime_immutable)'
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('updated_at')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD updated_at DATETIME DEFAULT NULL
                COMMENT '(DC2Type:datetime_immutable)'
            ");
        }

        if (!$schema->getTable('transaction')->hasColumn('deleted_at')) {
            $this->addSql("
                ALTER TABLE transaction
                ADD deleted_at DATETIME DEFAULT NULL
                COMMENT '(DC2Type:datetime_immutable)'
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Vehicle
        |--------------------------------------------------------------------------
        */

        if (!$schema->getTable('vehicle')->hasColumn('agency_id')) {
            $this->addSql("
                ALTER TABLE vehicle
                ADD agency_id INT DEFAULT NULL
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Estimation
        |--------------------------------------------------------------------------
        */

        if (!$schema->getTable('estimation')->hasColumn('agency_id')) {
            $this->addSql("
                ALTER TABLE estimation
                ADD agency_id INT DEFAULT NULL
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | Mise à jour des données
        |--------------------------------------------------------------------------
        */

        $this->addSql("
            UPDATE vehicle
            SET agency_id = (
                SELECT id
                FROM agency
                ORDER BY id ASC
                LIMIT 1
            )
            WHERE agency_id IS NULL
            OR agency_id = 0
        ");

        $this->addSql("
            UPDATE estimation e
            INNER JOIN vehicle v ON e.vehicle_id = v.id
            SET e.agency_id = v.agency_id
            WHERE e.agency_id IS NULL
            OR e.agency_id = 0
        ");

        $this->addSql("
            UPDATE transaction t
            INNER JOIN estimation e ON t.estimation_id = e.id
            SET t.agency_id = e.agency_id
            WHERE t.agency_id IS NULL
            OR t.agency_id = 0
        ");

        $this->addSql("
            UPDATE transaction t
            INNER JOIN estimation e ON t.estimation_id = e.id
            INNER JOIN vehicle v ON e.vehicle_id = v.id
            SET t.seller_id = v.seller_id
            WHERE t.seller_id IS NULL
            OR t.seller_id = 0
        ");

        $this->addSql("
            UPDATE transaction
            SET status = 'PENDING'
            WHERE status IS NULL
            OR status = ''
        ");

        $this->addSql("
            UPDATE transaction
            SET created_at = NOW()
            WHERE created_at IS NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | Nettoyage estimation invalide
        |--------------------------------------------------------------------------
        */

        $this->addSql("
            DELETE FROM estimation
            WHERE agency_id IS NULL
            OR agency_id = 0
            OR agency_id NOT IN (
                SELECT id FROM agency
            )
        ");

        /*
        |--------------------------------------------------------------------------
        | Colonnes NOT NULL
        |--------------------------------------------------------------------------
        */

        if ($schema->getTable('vehicle')->hasColumn('agency_id')) {
            $this->addSql("
                ALTER TABLE vehicle
                MODIFY agency_id INT NOT NULL
            ");
        }

        if ($schema->getTable('estimation')->hasColumn('agency_id')) {
            $this->addSql("
                ALTER TABLE estimation
                MODIFY agency_id INT NOT NULL
            ");
        }

        $this->addSql("
            ALTER TABLE transaction
            MODIFY seller_id INT NOT NULL,
            MODIFY agency_id INT NOT NULL,
            MODIFY status VARCHAR(50) NOT NULL,
            MODIFY created_at DATETIME NOT NULL
            COMMENT '(DC2Type:datetime_immutable)'
        ");
    }

    public function down(Schema $schema): void {}
}

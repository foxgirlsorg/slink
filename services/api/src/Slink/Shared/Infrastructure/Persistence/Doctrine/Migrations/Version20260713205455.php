<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713205455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add item_id index to collection_item table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_collection_item_item_id ON "collection_item" (item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_collection_item_item_id');
    }
}

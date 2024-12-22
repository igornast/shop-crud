<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222110957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customers (id UUID NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_62534E21E7927C74 ON customers (email)');
        $this->addSql('CREATE TABLE orders (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, customer_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E52FFDEE9395C3F3 ON orders (customer_id)');
        $this->addSql('CREATE TABLE orders_products (order_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY(order_id, product_id))');
        $this->addSql('CREATE INDEX IDX_749C879C8D9F6D38 ON orders_products (order_id)');
        $this->addSql('CREATE INDEX IDX_749C879C4584665A ON orders_products (product_id)');
        $this->addSql('CREATE TABLE products (id UUID NOT NULL, sku VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(40) NOT NULL, brand VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, price_amount VARCHAR(255) DEFAULT NULL, price_code VARCHAR(3) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_products ADD CONSTRAINT FK_749C879C8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE orders_products ADD CONSTRAINT FK_749C879C4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE9395C3F3');
        $this->addSql('ALTER TABLE orders_products DROP CONSTRAINT FK_749C879C8D9F6D38');
        $this->addSql('ALTER TABLE orders_products DROP CONSTRAINT FK_749C879C4584665A');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE orders_products');
        $this->addSql('DROP TABLE products');
    }
}

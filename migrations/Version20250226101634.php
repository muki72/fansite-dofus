<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226101634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guide_category (guide_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_33CDA7A1D7ED1D4B (guide_id), INDEX IDX_33CDA7A112469DE2 (category_id), PRIMARY KEY(guide_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE guide_category ADD CONSTRAINT FK_33CDA7A1D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guide (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guide_category ADD CONSTRAINT FK_33CDA7A112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guide ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE guide ADD CONSTRAINT FK_CA9EC735A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_CA9EC735A76ED395 ON guide (user_id)');
        $this->addSql('ALTER TABLE post ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE reply ADD post_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E04B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE reply ADD CONSTRAINT FK_FDA8C6E0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FDA8C6E04B89032C ON reply (post_id)');
        $this->addSql('CREATE INDEX IDX_FDA8C6E0A76ED395 ON reply (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guide_category DROP FOREIGN KEY FK_33CDA7A1D7ED1D4B');
        $this->addSql('ALTER TABLE guide_category DROP FOREIGN KEY FK_33CDA7A112469DE2');
        $this->addSql('DROP TABLE guide_category');
        $this->addSql('ALTER TABLE guide DROP FOREIGN KEY FK_CA9EC735A76ED395');
        $this->addSql('DROP INDEX IDX_CA9EC735A76ED395 ON guide');
        $this->addSql('ALTER TABLE guide DROP user_id');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395 ON post');
        $this->addSql('ALTER TABLE post DROP user_id');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E04B89032C');
        $this->addSql('ALTER TABLE reply DROP FOREIGN KEY FK_FDA8C6E0A76ED395');
        $this->addSql('DROP INDEX IDX_FDA8C6E04B89032C ON reply');
        $this->addSql('DROP INDEX IDX_FDA8C6E0A76ED395 ON reply');
        $this->addSql('ALTER TABLE reply DROP post_id, DROP user_id');
    }
}

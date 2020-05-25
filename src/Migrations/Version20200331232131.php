<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200331232131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ligne_commande CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_produit id_produit INT DEFAULT NULL, CHANGE id_commande id_commande INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resider CHANGE id_utilisateur id_utilisateur INT DEFAULT NULL, CHANGE id_adresse id_adresse INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ligne_commande CHANGE id id INT NOT NULL, CHANGE id_produit id_produit INT NOT NULL, CHANGE id_commande id_commande INT NOT NULL');
        $this->addSql('ALTER TABLE resider CHANGE id_utilisateur id_utilisateur INT NOT NULL, CHANGE id_adresse id_adresse INT NOT NULL');
    }
}

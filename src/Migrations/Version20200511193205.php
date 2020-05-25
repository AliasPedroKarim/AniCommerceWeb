<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200511193205 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE associer_categorie DROP FOREIGN KEY associer_categorie_ibfk_1');
        $this->addSql('ALTER TABLE associer_categorie DROP FOREIGN KEY associer_categorie_ibfk_2');
        $this->addSql('ALTER TABLE associer_categorie CHANGE id_produit id_produit INT DEFAULT NULL, CHANGE id_categorie id_categorie INT DEFAULT NULL');
        $this->addSql('ALTER TABLE associer_categorie ADD CONSTRAINT FK_30BA30AFF7384557 FOREIGN KEY (id_produit) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE associer_categorie ADD CONSTRAINT FK_30BA30AFC9486A13 FOREIGN KEY (id_categorie) REFERENCES categorie (id)');
        $this->addSql('CREATE UNIQUE INDEX id ON associer_categorie (id, id_produit, id_categorie)');
        $this->addSql('ALTER TABLE commande CHANGE id_utilisateur id_utilisateur INT DEFAULT NULL');
        $this->addSql('ALTER TABLE produit CHANGE id_magasin id_magasin INT DEFAULT NULL');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY magasin_ibfk_1');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY magasin_ibfk_2');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F272BB8456F FOREIGN KEY (id_image) REFERENCES image (id)');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F271DC2A166 FOREIGN KEY (id_adresse) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE magasin RENAME INDEX FK_54AF5F272BB8456F TO id_image');
        $this->addSql('ALTER TABLE magasin RENAME INDEX FK_54AF5F271DC2A166 TO id_adresse');
        $this->addSql('ALTER TABLE presenter CHANGE id_image id_image INT DEFAULT NULL, CHANGE id_produit id_produit INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD disabled TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE history_command CHANGE id_command id_command INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur_type DROP FOREIGN KEY table_name_utilisateur_id_fk');
        $this->addSql('ALTER TABLE utilisateur_type DROP FOREIGN KEY utilisateur_type_type_compte_id_fk');
        $this->addSql('ALTER TABLE utilisateur_type CHANGE id_utilisateur id_utilisateur INT DEFAULT NULL, CHANGE id_type_compte id_type_compte INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur_type ADD CONSTRAINT FK_455F3B1350EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur_type ADD CONSTRAINT FK_455F3B13F433F4AF FOREIGN KEY (id_type_compte) REFERENCES type_compte (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE associer_categorie DROP FOREIGN KEY FK_30BA30AFF7384557');
        $this->addSql('ALTER TABLE associer_categorie DROP FOREIGN KEY FK_30BA30AFC9486A13');
        $this->addSql('DROP INDEX id ON associer_categorie');
        $this->addSql('ALTER TABLE associer_categorie CHANGE id_produit id_produit INT NOT NULL, CHANGE id_categorie id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE associer_categorie ADD CONSTRAINT associer_categorie_ibfk_1 FOREIGN KEY (id_produit) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE associer_categorie ADD CONSTRAINT associer_categorie_ibfk_2 FOREIGN KEY (id_categorie) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande CHANGE id_utilisateur id_utilisateur INT NOT NULL');
        $this->addSql('ALTER TABLE history_command CHANGE id_command id_command INT NOT NULL');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F272BB8456F');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F271DC2A166');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT magasin_ibfk_1 FOREIGN KEY (id_image) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT magasin_ibfk_2 FOREIGN KEY (id_adresse) REFERENCES adresse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE magasin RENAME INDEX id_adresse TO magasin_ibfk_2');
        $this->addSql('ALTER TABLE magasin RENAME INDEX id_image TO magasin_ibfk_1');
        $this->addSql('ALTER TABLE presenter CHANGE id_image id_image INT NOT NULL, CHANGE id_produit id_produit INT NOT NULL');
        $this->addSql('ALTER TABLE produit CHANGE id_magasin id_magasin INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP disabled');
        $this->addSql('ALTER TABLE utilisateur_type DROP FOREIGN KEY FK_455F3B1350EAE44');
        $this->addSql('ALTER TABLE utilisateur_type DROP FOREIGN KEY FK_455F3B13F433F4AF');
        $this->addSql('ALTER TABLE utilisateur_type CHANGE id_utilisateur id_utilisateur INT NOT NULL, CHANGE id_type_compte id_type_compte INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur_type ADD CONSTRAINT table_name_utilisateur_id_fk FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_type ADD CONSTRAINT utilisateur_type_type_compte_id_fk FOREIGN KEY (id_type_compte) REFERENCES type_compte (id) ON DELETE CASCADE');
    }
}

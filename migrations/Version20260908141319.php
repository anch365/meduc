<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908141319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affectation (id INT AUTO_INCREMENT NOT NULL, classe VARCHAR(50) NOT NULL, matiere VARCHAR(50) NOT NULL, date_debut DATE NOT NULL, date_fin DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, enseignant_id INT NOT NULL, etablissement_id INT NOT NULL, INDEX IDX_F4DD61D3E455FCC0 (enseignant_id), INDEX IDX_F4DD61D3FF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE enseignant (id INT AUTO_INCREMENT NOT NULL, genre VARCHAR(10) NOT NULL, date_naissance DATE NOT NULL, lieu_naissance VARCHAR(100) DEFAULT NULL, niveau_enseignement VARCHAR(50) DEFAULT NULL, statut_professionnel VARCHAR(30) NOT NULL, photo VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, localite_id INT DEFAULT NULL, utilisateur_id INT NOT NULL, INDEX IDX_81A72FA1924DD2B5 (localite_id), UNIQUE INDEX UNIQ_81A72FA1FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, type VARCHAR(50) NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, localite_id INT NOT NULL, INDEX IDX_20FD592C924DD2B5 (localite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE localite (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, ile VARCHAR(30) NOT NULL, UNIQUE INDEX uq_localite_nom_ile (nom, ile), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, matricule VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(180) DEFAULT NULL, actif TINYINT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, dernier_connexion DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_MATRICULE (matricule), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D3E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D3FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE enseignant ADD CONSTRAINT FK_81A72FA1924DD2B5 FOREIGN KEY (localite_id) REFERENCES localite (id)');
        $this->addSql('ALTER TABLE enseignant ADD CONSTRAINT FK_81A72FA1FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592C924DD2B5 FOREIGN KEY (localite_id) REFERENCES localite (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D3E455FCC0');
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D3FF631228');
        $this->addSql('ALTER TABLE enseignant DROP FOREIGN KEY FK_81A72FA1924DD2B5');
        $this->addSql('ALTER TABLE enseignant DROP FOREIGN KEY FK_81A72FA1FB88E14F');
        $this->addSql('ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592C924DD2B5');
        $this->addSql('DROP TABLE affectation');
        $this->addSql('DROP TABLE enseignant');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE localite');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

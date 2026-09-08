<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908201925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
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
    }
}

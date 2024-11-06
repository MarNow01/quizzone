<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106182531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attempt_question (id INT AUTO_INCREMENT NOT NULL, attempt_quiz_id INT NOT NULL, question_id INT NOT NULL, date_of_creation DATETIME NOT NULL, answered_answer VARCHAR(255) DEFAULT NULL, INDEX IDX_DEEEBFE841798FA7 (attempt_quiz_id), INDEX IDX_DEEEBFE81E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attempt_quiz (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, user_id INT NOT NULL, date_of_creation DATETIME NOT NULL, INDEX IDX_67AF7172853CD175 (quiz_id), INDEX IDX_67AF7172A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attempt_question ADD CONSTRAINT FK_DEEEBFE841798FA7 FOREIGN KEY (attempt_quiz_id) REFERENCES attempt_quiz (id)');
        $this->addSql('ALTER TABLE attempt_question ADD CONSTRAINT FK_DEEEBFE81E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE attempt_quiz ADD CONSTRAINT FK_67AF7172853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE attempt_quiz ADD CONSTRAINT FK_67AF7172A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE question CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE answer_c answer_c VARCHAR(255) DEFAULT NULL, CHANGE answer_d answer_d VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attempt_question DROP FOREIGN KEY FK_DEEEBFE841798FA7');
        $this->addSql('ALTER TABLE attempt_question DROP FOREIGN KEY FK_DEEEBFE81E27F6BF');
        $this->addSql('ALTER TABLE attempt_quiz DROP FOREIGN KEY FK_67AF7172853CD175');
        $this->addSql('ALTER TABLE attempt_quiz DROP FOREIGN KEY FK_67AF7172A76ED395');
        $this->addSql('DROP TABLE attempt_question');
        $this->addSql('DROP TABLE attempt_quiz');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE question CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE answer_c answer_c VARCHAR(255) DEFAULT \'NULL\', CHANGE answer_d answer_d VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}

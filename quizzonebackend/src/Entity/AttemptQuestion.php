<?php

namespace App\Entity;

use App\Repository\AttemptQuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttemptQuestionRepository::class)]
class AttemptQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateOfCreation = null;

    #[ORM\ManyToOne(inversedBy: 'attemptQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AttemptQuiz $AttemptQuiz = null;

    #[ORM\ManyToOne(inversedBy: 'attemptQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $Question = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $AnsweredAnswer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateOfCreation(): ?\DateTimeInterface
    {
        return $this->DateOfCreation;
    }

    public function setDateOfCreation(\DateTimeInterface $DateOfCreation): static
    {
        $this->DateOfCreation = $DateOfCreation;

        return $this;
    }

    public function getAttemptQuiz(): ?AttemptQuiz
    {
        return $this->AttemptQuiz;
    }

    public function setAttemptQuiz(?AttemptQuiz $AttemptQuiz): static
    {
        $this->AttemptQuiz = $AttemptQuiz;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->Question;
    }

    public function setQuestion(?Question $Question): static
    {
        $this->Question = $Question;

        return $this;
    }

    public function getAnsweredAnswer(): ?string
    {
        return $this->AnsweredAnswer;
    }

    public function setAnsweredAnswer(?string $AnsweredAnswer): static
    {
        $this->AnsweredAnswer = $AnsweredAnswer;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4096)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $answerA = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $answerB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $answerC = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $answerD = null;

    #[ORM\Column(length: 255)]
    private ?string $correctAnswer = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    /**
     * @var Collection<int, AttemptQuestion>
     */
    #[ORM\OneToMany(targetEntity: AttemptQuestion::class, mappedBy: 'Question', orphanRemoval: true)]
    private Collection $attemptQuestions;

    #[ORM\Column]
    private ?bool $IsTrueOrFalse = null;

    #[ORM\Column(nullable: true)]
    private ?int $TimeLimit = null;

    #[ORM\Column]
    private ?bool $IsOpen = null;

    public function __construct()
    {
        $this->attemptQuestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getAnswerA(): ?string
    {
        return $this->answerA;
    }

    public function setAnswerA(?string $answerA): static
    {
        $this->answerA = $answerA;

        return $this;
    }

    public function getAnswerB(): ?string
    {
        return $this->answerB;
    }

    public function setAnswerB(?string $answerB): static
    {
        $this->answerB = $answerB;

        return $this;
    }

    public function getAnswerC(): ?string
    {
        return $this->answerC;
    }

    public function setAnswerC(?string $answerC): static
    {
        $this->answerC = $answerC;

        return $this;
    }

    public function getAnswerD(): ?string
    {
        return $this->answerD;
    }

    public function setAnswerD(?string $answerD): static
    {
        $this->answerD = $answerD;

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): static
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * @return Collection<int, AttemptQuestion>
     */
    public function getAttemptQuestions(): Collection
    {
        return $this->attemptQuestions;
    }

    public function addAttemptQuestion(AttemptQuestion $attemptQuestion): static
    {
        if (!$this->attemptQuestions->contains($attemptQuestion)) {
            $this->attemptQuestions->add($attemptQuestion);
            $attemptQuestion->setQuestion($this);
        }

        return $this;
    }

    public function removeAttemptQuestion(AttemptQuestion $attemptQuestion): static
    {
        if ($this->attemptQuestions->removeElement($attemptQuestion)) {
            // set the owning side to null (unless already changed)
            if ($attemptQuestion->getQuestion() === $this) {
                $attemptQuestion->setQuestion(null);
            }
        }

        return $this;
    }

    public function isTrueOrFalse(): ?bool
    {
        return $this->IsTrueOrFalse;
    }

    public function setTrueOrFalse(bool $IsTrueOrFalse): static
    {
        $this->IsTrueOrFalse = $IsTrueOrFalse;

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->TimeLimit;
    }

    public function setTimeLimit(?int $TimeLimit): static
    {
        $this->TimeLimit = $TimeLimit;

        return $this;
    }

    public function isOpen(): ?bool
    {
        return $this->IsOpen;
    }

    public function setOpen(bool $IsOpen): static
    {
        $this->IsOpen = $IsOpen;

        return $this;
    }
}

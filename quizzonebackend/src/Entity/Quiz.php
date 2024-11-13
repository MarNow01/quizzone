<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOfCreation = null;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $questions;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    private ?Category $category = null;

    /**
     * @var Collection<int, AttemptQuiz>
     */
    #[ORM\OneToMany(targetEntity: AttemptQuiz::class, mappedBy: 'Quiz', orphanRemoval: true)]
    private Collection $attemptQuizzes;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->attemptQuizzes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateOfCreation(): ?\DateTimeInterface
    {
        return $this->dateOfCreation;
    }

    public function setDateOfCreation(\DateTimeInterface $dateOfCreation): static
    {
        $this->dateOfCreation = $dateOfCreation;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, AttemptQuiz>
     */
    public function getAttemptQuizzes(): Collection
    {
        return $this->attemptQuizzes;
    }

    public function addAttemptQuiz(AttemptQuiz $attemptQuiz): static
    {
        if (!$this->attemptQuizzes->contains($attemptQuiz)) {
            $this->attemptQuizzes->add($attemptQuiz);
            $attemptQuiz->setQuiz($this);
        }

        return $this;
    }

    public function removeAttemptQuiz(AttemptQuiz $attemptQuiz): static
    {
        if ($this->attemptQuizzes->removeElement($attemptQuiz)) {
            // set the owning side to null (unless already changed)
            if ($attemptQuiz->getQuiz() === $this) {
                $attemptQuiz->setQuiz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setQuiz($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getQuiz() === $this) {
                $comment->setQuiz(null);
            }
        }

        return $this;
    }
}

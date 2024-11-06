<?php

namespace App\Entity;

use App\Repository\AttemptQuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttemptQuizRepository::class)]
class AttemptQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateOfCreation = null;

    #[ORM\ManyToOne(inversedBy: 'attemptQuizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $Quiz = null;

    #[ORM\ManyToOne(inversedBy: 'attemptQuizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    /**
     * @var Collection<int, AttemptQuestion>
     */
    #[ORM\OneToMany(targetEntity: AttemptQuestion::class, mappedBy: 'AttemptQuiz', orphanRemoval: true)]
    private Collection $attemptQuestions;

    public function __construct()
    {
        $this->attemptQuestions = new ArrayCollection();
    }

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

    public function getQuiz(): ?Quiz
    {
        return $this->Quiz;
    }

    public function setQuiz(?Quiz $Quiz): static
    {
        $this->Quiz = $Quiz;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

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
            $attemptQuestion->setAttemptQuiz($this);
        }

        return $this;
    }

    public function removeAttemptQuestion(AttemptQuestion $attemptQuestion): static
    {
        if ($this->attemptQuestions->removeElement($attemptQuestion)) {
            // set the owning side to null (unless already changed)
            if ($attemptQuestion->getAttemptQuiz() === $this) {
                $attemptQuestion->setAttemptQuiz(null);
            }
        }

        return $this;
    }
}

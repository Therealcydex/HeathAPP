<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\QuizTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: QuizRepository::class)]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz')]
    private Collection $questions;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The quiz name should not be blank.")]
    private ?string $name = null;

    #[ORM\Column(type: "string", length: 50)] // Store as a string, not enumType
    #[Assert\NotBlank(message: "The quiz type should not be blank.")]
    private ?string $type = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?QuizTypeEnum
    {
        return QuizTypeEnum::tryFrom($this->type);
    }

    public function setType(QuizTypeEnum $type): self
    {
        $this->type = $type->value; // Store as string
        return $this;
    }

    public function __toString(): string
    {
        return $this->name; // Now Symfony can display the question text in the dropdown
    }
}

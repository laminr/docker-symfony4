<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FocusRepository")
 */
class Focus
{

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="App\Entity\Question", inversedBy="care", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $care;

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getCare(): ?bool
    {
        return $this->care;
    }

    public function setCare(bool $care): self
    {
        $this->care = $care;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowRepository")
 */
class Follow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Question", inversedBy="follow", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $good;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $wrong;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Follow constructor.
     * @param $question
     * @param $user
     */
    public function __construct($user, $question)
    {
        $this->question = $question;
        $this->user = $user;
    }

    public function increment($good = true)
    {
        if ($good) {
            $this->good += 1;
        } else {
            $this->wrong += 1;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getGood(): ?int
    {
        return $this->good;
    }

    public function setGood(int $good): self
    {
        $this->good = $good;

        return $this;
    }

    public function getWrong(): ?int
    {
        return $this->wrong;
    }

    public function setWrong(int $wrong): self
    {
        $this->wrong = $wrong;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
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

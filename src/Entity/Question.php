<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $canonical;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $chapter;

    /**
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    private $refImg;

    /**
     * @ORM\Column(name="explaination", nullable=true, type="text")
     */
    private $explain;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Topic", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $topic;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Focus", mappedBy="question", cascade={"persist", "remove"})
     */
    private $focus;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Follow", mappedBy="question", cascade={"persist", "remove"})
     */
    private $follow;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", mappedBy="questions")
     */
    private $tags;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function setCanonical(string $canonical): self
    {
        $this->canonical = $canonical;

        return $this;
    }


    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getChapter(): ?string
    {
        return $this->chapter;
    }

    public function setChapter(?string $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getRefImg(): ?string
    {
        return $this->refImg;
    }

    public function setRefImg(?string $refImg): self
    {
        $this->refImg = $refImg;

        return $this;
    }

    public function getExplain(): ?string
    {
        return $this->explain;
    }

    public function setExplain(string $explain): self
    {
        $this->explain = $explain;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getFocus(): ?Focus
    {
        return $this->focus;
    }

    public function setFocus(?Focus $focus): self
    {
        $this->focus = $focus;

        if ($focus !== null) {
            // set the owning side of the relation if necessary
            if ($this !== $focus->getQuestion()) {
                $focus->setQuestion($this);
            }
        }

        return $this;
    }

    public function getFollow(): ?Follow
    {
        return $this->follow;
    }

    public function setFollow(?Follow $follow): self
    {
        $this->follow = $follow;

        if ($follow !== null) {
            // set the owning side of the relation if necessary
            if ($this !== $follow->getQuestion()) {
                $follow->setQuestion($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addQuestion($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeQuestion($this);
        }

        return $this;
    }


}

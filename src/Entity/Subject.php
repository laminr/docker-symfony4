<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubjectRepository")
 */
class Subject
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Source", inversedBy="subjects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $source;


    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Topic", mappedBy="subject")
     */
    private $topics;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="authorisedSubjects")
     */
    private $authorisedUsers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="auhtorisedAdmins")
     */
    private $authorisedAdmins;


    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->authorisedUsers = new ArrayCollection();
        $this->authorisedAdmins = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(?Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): self
    {
        if (!$this->topics->contains($topic)) {
            $this->topics[] = $topic;
            $topic->setSubject($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): self
    {
        if ($this->topics->contains($topic)) {
            $this->topics->removeElement($topic);
            // set the owning side to null (unless already changed)
            if ($topic->getSubject() === $this) {
                $topic->setSubject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAuthorisedUsers(): Collection
    {
        return $this->authorisedUsers;
    }

    public function addAuthorisedUsers(User $user): self
    {
        if (!$this->authorisedUsers->contains($user)) {
            $this->authorisedUsers[] = $user;
            $user->addAuthorisedSubject($this);
        }

        return $this;
    }

    public function removeAuthorisedUsers(User $user): self
    {
        if ($this->authorisedUsers->contains($user)) {
            $this->authorisedUsers->removeElement($user);
            $user->removeAuthorisedSubject($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAuthorisedAdmins(): Collection
    {
        return $this->authorisedAdmins;
    }

    public function addAuthorisedAdmin(User $authorisedAdmin): self
    {
        if (!$this->authorisedAdmins->contains($authorisedAdmin)) {
            $this->authorisedAdmins[] = $authorisedAdmin;
            $authorisedAdmin->addAuhtorisedAdmin($this);
        }

        return $this;
    }

    public function removeAuthorisedAdmin(User $authorisedAdmin): self
    {
        if ($this->authorisedAdmins->contains($authorisedAdmin)) {
            $this->authorisedAdmins->removeElement($authorisedAdmin);
            $authorisedAdmin->removeAuhtorisedAdmin($this);
        }

        return $this;
    }

}

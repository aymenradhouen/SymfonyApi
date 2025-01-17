<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Article
{
    use TimeStampTrait;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("userArticle")
     * @Groups("profileArticles")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("userArticle")
     * @Assert\NotBlank(message="Please enter a title !")
     * @Groups("profileArticles")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter a title !")
     * @Groups("userArticle")
     * @Groups("profileArticles")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter a title !")
     * @Groups("userArticle")
     * @Groups("profileArticles")
     */
    private $image;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="articles")
     * @Groups("userArticle")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="article", orphanRemoval=true)
     * @Groups("userArticle")
     */
    private $comments;


    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("userArticle")
     */
    private $createdBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Likes", mappedBy="articles", orphanRemoval=true)
     */
    private $likess;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likess = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }


    /**
     * @return Collection|Likes[]
     */
    public function getLikess(): Collection
    {
        return $this->likess;
    }

    public function addLikess(Likes $likess): self
    {
        if (!$this->likess->contains($likess)) {
            $this->likess[] = $likess;
            $likess->setArticles($this);
        }

        return $this;
    }

    public function removeLikess(Likes $likess): self
    {
        if ($this->likess->contains($likess)) {
            $this->likess->removeElement($likess);
            // set the owning side to null (unless already changed)
            if ($likess->getArticles() === $this) {
                $likess->setArticles(null);
            }
        }

        return $this;
    }
}

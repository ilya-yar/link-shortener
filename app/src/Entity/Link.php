<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'link')]
#[ORM\Index(name: 'search_hash_idx', columns: ['search_hash'])]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2048)]
    private ?string $original_url = null;

    #[ORM\Column(length: 32)]
    private ?string $search_hash = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $link_hash = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalUrl(): ?string
    {
        return $this->original_url;
    }

    public function setOriginalUrl(string $original_url): static
    {
        $this->original_url = $original_url;

        return $this;
    }

    public function getLinkHash(): ?string
    {
        return $this->link_hash;
    }

    public function setLinkHash(string $link_hash): static
    {
        $this->link_hash = $link_hash;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        // При сохранении модели создаем md5 хэш ссылки, по нему будет производиться поиск с использованием индекса.
        $this->search_hash = md5($this->original_url);
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id,ORM\GeneratedValue]
    #[ORM\Column,Groups(['category:read', 'product:read'])]
    private ?int $id = null;

    #[
        ORM\Column(length: 255),
        Groups(['product:read', 'category:read', 'category:write']),
        Assert\NotBlank
    ]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true),
        Groups(['category:read', 'category:write'])]
    private ?string $comment = null;

    public function getId(): ?int
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }
}

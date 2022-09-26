<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\DiscriminatorColumn(name:"type", type:"string", length: 8),
    UniqueEntity(fields: ['email'], message: 'There is already an account with this email')
]
class User implements UserInterface, JWTUserInterface, PasswordAuthenticatedUserInterface
{
    const DEFAULT_ROLE = 'ROLE_USER';

    #[ORM\Id, ORM\Column(type: 'ulid', unique: true),
        ORM\GeneratedValue(strategy: 'CUSTOM'), Groups(['user:read']),
        ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private ?Ulid $id;

    #[ORM\Column(length: 255),Assert\NotBlank,
        Groups(['user:read', 'user:write'])]
    private ?string $name;

    #[ORM\Column(length: 255, nullable: true),
        Groups(['user:read', 'user:write'])]
    private ?string $phone = null;

    #[
        ORM\Column(length: 180, unique: true),
        Assert\Email,Assert\NotBlank,
        Groups(['user:read', 'user:write'])
    ]
    private ?string $email;

    #[ORM\Column(nullable: true),
        Groups(['user:read_full'])]
    private ?bool $enabled = null;

    #[ORM\Column, Groups(['user:read','user:write'])]
    protected array $roles;

    #[ORM\Column,Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true),
        Groups(['user:read', 'user:write'])]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true),
        Groups(['user:read_full'])]
    private ?string $confirmaToken = null;

    #[ORM\Column(length: 255, nullable: true),
        Groups(['user:read_full'])]
    private ?string $lastLoginIp = null;

    #[ORM\Column(nullable: true), Groups(['user:read'])]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column,Groups(['user:read_full'])]
    private ?\DateTimeImmutable $createdAt;

    public function __construct(?string $username = null, array $roles = [], $id = null, $name = null)
    {
        $this->email = $username;
        $this->roles = $roles;
        $this->name = $name;
        $this->createdAt = new \DateTimeImmutable();
        $this->id = $id;
    }

    public static function createFromPayload($username, array $payload): User
    {
        return new self(
            $username,
            $payload['roles'],
            $payload['id'],
            $payload['name']
        );
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getConfirmaToken(): ?string
    {
        return $this->confirmaToken;
    }

    public function setConfirmaToken(?string $confirmaToken): self
    {
        $this->confirmaToken = $confirmaToken;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): self
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = self::DEFAULT_ROLE;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // $this->plainPassword = null; If you store any temporary, sensitive data on the user, clear it here
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

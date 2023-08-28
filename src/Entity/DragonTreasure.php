<?php

namespace App\Entity;

use App\Repository\DragonTreasureRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * The estimated value of this treasure, in gold coins.
     */
    #[ORM\Column]
    private ?int $value = 0;

    #[ORM\Column]
    private ?int $coolFactor = 0;

    #[ORM\Column]
    private \DateTimeImmutable $plunderedAt;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'dragonTreasures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var bool Non-persisted property to help determine if the treasure is owned by the authenticated user
     */
    private bool $isOwnedByAuthenticatedUser = false;

    public function __construct(string $name = null)
    {
        $this->name = $name;
        $this->plunderedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getShortDescription(): string
    {
        return u($this->getDescription())->truncate(40, '...');
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): ?int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(int $coolFactor): self
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getPlunderedAt(): ?\DateTimeImmutable
    {
        return $this->plunderedAt;
    }

    public function setPlunderedAt(\DateTimeImmutable $plunderedAt): self
    {
        $this->plunderedAt = $plunderedAt;

        return $this;
    }

    /**
     * A human-readable representation of when this treasure was plundered.
     */
    public function getPlunderedAtAgo(): string
    {
        return Carbon::instance($this->plunderedAt)->diffForHumans();
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function isOwnedByAuthenticatedUser(): bool
    {
        if (!isset($this->isOwnedByAuthenticatedUser)) {
            throw new \LogicException('You must call setIsOwnedByAuthenticatedUser() before isOwnedByAuthenticatedUser()');
        }

        return $this->isOwnedByAuthenticatedUser;
    }

    public function setIsOwnedByAuthenticatedUser(bool $isOwnedByAuthenticatedUser): void
    {
        $this->isOwnedByAuthenticatedUser = $isOwnedByAuthenticatedUser;
    }
}

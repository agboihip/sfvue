<?php

namespace App\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\InputBag;

class Search
{
    private ?int $maxPrice;
    private ?string $keyword;
    private Collection $inOptions;
    private ?\DateTimeInterface $postedAt;

    public function __construct(?int $maxPrice = null, ?string $keyword = null, ?\DateTime $postedAt = null, Collection $inOptions = new ArrayCollection())
    {
        $this->maxPrice = $maxPrice;
        $this->keyword = $keyword;
        $this->postedAt = $postedAt;
        $this->inOptions = $inOptions;
    }

    public static function createFrom(InputBag $value): Search
    {
        return new self(
            $value->getInt('maxpr'),
            $value->get('kword'),
            $value->get('datep'),
            new ArrayCollection((array)$value->get('tags')) //explode(',', )
        );
    }

    public function getFromShop(): ?int
    {
        return $this->fromShop;
    }

    public function setFromShop(?int $fromShop): self
    {
        $this->fromShop = $fromShop;

        return $this;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?int $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getInOptions(): ?Collection
    {
        return $this->inOptions;
    }

    public function setInOptions(Collection $inOptions): self
    {
        $this->inOptions = $inOptions;

        return $this;
    }

    public function getPostedAt(): ?\DateTimeInterface
    {
        return $this->postedAt;
    }

    public function setPostedAt(?\DateTimeInterface $postedAt): Search
    {
        $this->postedAt = $postedAt;
        return $this;
    }
}
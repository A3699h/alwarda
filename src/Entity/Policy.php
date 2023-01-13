<?php

namespace App\Entity;

use App\Repository\PolicyRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PolicyRepository::class)
 */
class Policy
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacyEn;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $privacyAr;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrivacyEn(): ?string
    {
        return $this->privacyEn;
    }

    public function setPrivacyEn(?string $privacyEn): self
    {
        $this->privacyEn = $privacyEn;

        return $this;
    }

    public function getPrivacyAr(): ?string
    {
        return $this->privacyAr;
    }

    public function setPrivacyAr(?string $privacyAr): self
    {
        $this->privacyAr = $privacyAr;

        return $this;
    }
}

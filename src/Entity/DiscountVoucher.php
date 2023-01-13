<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiscountVoucherRepository")
 */
class DiscountVoucher
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"client"})
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThanOrEqual("today"))
     * @Groups({"client"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThanOrEqual("today"))
     * @Assert\GreaterThan(propertyPath="startDate")
     * @Groups({"client"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="float")
     * @Groups({"client"})
     */
    private $discountPercentage;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxUse;

    /**
     * @ORM\Column(type="integer")
     */
    private $clientMaxUse;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="discountVoucher")
     */
    private $orders;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $uses;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDiscountPercentage(): ?float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(float $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;

        return $this;
    }

    public function getMaxUse(): ?int
    {
        return $this->maxUse;
    }

    public function setMaxUse(int $maxUse): self
    {
        $this->maxUse = $maxUse;

        return $this;
    }

    public function getClientMaxUse(): ?int
    {
        return $this->clientMaxUse;
    }

    public function setClientMaxUse(int $clientMaxUse): self
    {
        $this->clientMaxUse = $clientMaxUse;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setDiscountVoucher($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getDiscountVoucher() === $this) {
                $order->setDiscountVoucher(null);
            }
        }

        return $this;
    }

    public function getUses(): ?int
    {
        return $this->uses;
    }

    public function setUses(?int $uses): self
    {
        $this->uses = $uses;

        return $this;
    }

}

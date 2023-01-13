<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SlotRepository")
 */
class Slot
{

    const TYPES = ['delivery'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"client", "slot"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"slot"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxOrders = 0;

    /**
     * @ORM\Column(type="time")
     * @Groups({"slot"})
     * @JMS\Type("DateTime<'H:i'>")
     */
    private $showAt;

    /**
     * @ORM\Column(type="time")
     * @Groups({"slot"})
     * @JMS\Type("DateTime<'H:i'>")
     */
    private $closeAt;

    /**
     * @ORM\Column(type="time")
     * @Groups({"client", "delivery"})
     * @JMS\Type("DateTime<'H:i'>")
     */
    private $deliveryAt;

    /**
     * @ORM\Column(type="time")
     * @Groups({"client", "delivery"})
     * @JMS\Type("DateTime<'H:i'>")
     */
    private $deliveryTo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"slot"})
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $saturday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $sunday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $monday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $tuesday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $wednesday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $thursday;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $friday;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="deliverySlot")
     */
    private $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getMaxOrders(): ?int
    {
        return $this->maxOrders;
    }

    public function setMaxOrders(int $maxOrders): self
    {
        $this->maxOrders = $maxOrders;

        return $this;
    }

    public function getShowAt(): ?\DateTimeInterface
    {
        return $this->showAt;
    }

    public function setShowAt(\DateTimeInterface $showAt): self
    {
        $this->showAt = $showAt;

        return $this;
    }

    public function getCloseAt(): ?\DateTimeInterface
    {
        return $this->closeAt;
    }

    public function setCloseAt(\DateTimeInterface $closeAt): self
    {
        $this->closeAt = $closeAt;

        return $this;
    }

    public function getDeliveryAt(): ?\DateTimeInterface
    {
        return $this->deliveryAt;
    }

    public function setDeliveryAt(\DateTimeInterface $deliveryAt): self
    {
        $this->deliveryAt = $deliveryAt;

        return $this;
    }

    public function getDeliveryTo(): ?\DateTimeInterface
    {
        return $this->deliveryTo;
    }

    public function setDeliveryTo(\DateTimeInterface $deliveryTo): self
    {
        $this->deliveryTo = $deliveryTo;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(?bool $saturday): self
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function getSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(?bool $sunday): self
    {
        $this->sunday = $sunday;

        return $this;
    }

    public function getMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(?bool $monday): self
    {
        $this->monday = $monday;

        return $this;
    }

    public function getTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(?bool $tuesday): self
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function getWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(?bool $wednesday): self
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function getThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(?bool $thursday): self
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function getFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(?bool $friday): self
    {
        $this->friday = $friday;

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
            $order->setDeliverySlot($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getDeliverySlot() === $this) {
                $order->setDeliverySlot(null);
            }
        }

        return $this;
    }
}

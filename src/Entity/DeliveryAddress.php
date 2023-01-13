<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeliveryAddressRepository")
 */
class DeliveryAddress
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client", "basicClient"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deliveryAdresses")
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $recieverName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $recieverPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $recieverFullAddress;

    /**
     * @ORM\ManyToOne(targetEntity=Area::class, inversedBy="deliveryAdresses")
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $recieverArea;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $recieverLocations;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"client", "delivery", "basicClient"})
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="deliveryAddress")
     */
    private $orders;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active = true;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getRecieverName(): ?string
    {
        return $this->recieverName;
    }

    public function setRecieverName(?string $recieverName): self
    {
        $this->recieverName = $recieverName;

        return $this;
    }

    public function getRecieverPhone(): ?string
    {
        return $this->recieverPhone;
    }

    public function setRecieverPhone(?string $recieverPhone): self
    {
        $this->recieverPhone = $recieverPhone;

        return $this;
    }

    public function getRecieverFullAddress(): ?string
    {
        return $this->recieverFullAddress;
    }

    public function setRecieverFullAddress(?string $recieverFullAddress): self
    {
        $this->recieverFullAddress = $recieverFullAddress;

        return $this;
    }

    public function getRecieverArea(): ?Area
    {
        return $this->recieverArea;
    }

    public function setRecieverArea(?Area $recieverArea): self
    {
        $this->recieverArea = $recieverArea;

        return $this;
    }

    public function getRecieverLocations(): ?string
    {
        return $this->recieverLocations;
    }

    public function setRecieverLocations(?string $recieverLocations): self
    {
        $this->recieverLocations = $recieverLocations;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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
            $order->setDeliveryAddress($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getDeliveryAddress() === $this) {
                $order->setDeliveryAddress(null);
            }
        }

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

}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AreaRepository")
 */
class Area
{

    const MAP_NAMES = [
        "Najran",
        "Ar Riyad",
        "Ash Sharqiyah",
        "Al Madinah",
        "Al Quassim",
        "Ha'il",
        "Tabuk",
        "Al Hudud ash Shamaliyah",
        "Al Jawf",
        "Al Bahah",
        "`Asir",
        "Jizan",
        "Makkah"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client", "driver", "area", "basicClient"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"area", "client", "delivery", "basicClient"})
     */
    private $nameEn;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"area", "client", "delivery", "basicClient"})
     */
    private $nameAr;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active = true;

    /**
     * @ORM\Column(type="float")
     * @JMS\Groups({"area", "client"})
     */
    private $deliveryPrice = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="area")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryAddress::class, mappedBy="recieverArea")
     */
    private $deliveryAddresses;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mapName;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->deliveryAddresses = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): self
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getNameAr(): ?string
    {
        return $this->nameAr;
    }

    public function setNameAr(string $nameAr): self
    {
        $this->nameAr = $nameAr;

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

    public function getDeliveryPrice(): ?float
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(float $deliveryPrice): self
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setArea($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getArea() === $this) {
                $user->setArea(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nameEn . ' - ' . $this->getNameAr();
    }

    /**
     * @return Collection|DeliveryAddress[]
     */
    public function getDeliveryAddresses(): Collection
    {
        return $this->deliveryAddresses;
    }

    public function addDeliveryAdress(DeliveryAddress $deliveryAdress): self
    {
        if (!$this->deliveryAddresses->contains($deliveryAdress)) {
            $this->deliveryAddresses[] = $deliveryAdress;
            $deliveryAdress->setRecieverArea($this);
        }

        return $this;
    }

    public function removeDeliveryAdress(DeliveryAddress $deliveryAdress): self
    {
        if ($this->deliveryAddresses->contains($deliveryAdress)) {
            $this->deliveryAddresses->removeElement($deliveryAdress);
            // set the owning side to null (unless already changed)
            if ($deliveryAdress->getRecieverArea() === $this) {
                $deliveryAdress->setRecieverArea(null);
            }
        }

        return $this;
    }

    public function getMapName(): ?string
    {
        return $this->mapName;
    }

    public function setMapName(string $mapName): self
    {
        $this->mapName = $mapName;

        return $this;
    }
}

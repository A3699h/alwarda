<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 * @UniqueEntity("phone")
 */
class User implements UserInterface
{

    use TimestampableEntity;

    const USER_ROLES = [
        'shop' => 'ROLE_SHOP',
        'admin' => 'ROLE_ADMIN',
        'superAdmin' => 'ROLE_SUPER_ADMIN',
        'client' => 'ROLE_CLIENT',
        'driver' => 'ROLE_DRIVER',
        'unverified' => 'ROLE_UNVERIFIED'
    ];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client","driver", "basicClient", "default"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     * @JMS\Groups({"client", "basicClient"})
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"client","driver", "basicClient", "default"})
     */
    private $role = self::USER_ROLES['client'];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client","driver", "delivery", "basicClient"})
     */
    private $fullName;


    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client","driver", "delivery", "basicClient"})
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accessId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Area", inversedBy="users")
     * @JMS\Groups({"driver", "delivery", "client"})
     */
    private $area;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Product", inversedBy="users")
     */
    private $availableProducts;

    /**
     * @ORM\OneToMany(targetEntity=OrderNote::class, mappedBy="user")
     */
    private $orderNotes;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryAddress::class, mappedBy="client", cascade={"all"})
     */
    private $deliveryAddresses;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="client")
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="driver", cascade={"persist"})
     */
    private $driverOrders;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastVisit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @JMS\Groups({"client", "basicClient"})
     */
    private $balance = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $verificationCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $plainRole;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender")
     */
    private $sentMessages;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="reciever")
     */
    private $recievedMessages;


    public function __construct()
    {
        $this->availableProducts = new ArrayCollection();
        $this->orderNotes = new ArrayCollection();
        $this->deliveryAddresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->driverOrders = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->recievedMessages = new ArrayCollection();
    }


    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getId(): ?int
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }


    public function getRole(): string
    {
        $role = $this->role ?? self::USER_ROLES['client'];

        return $role;
    }

    public function getRoles()
    {
        return [$this->getRole()];
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(?string $password): self
    {
        if (!is_null($password)) {
            $this->password = $password;
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getAccessId(): ?string
    {
        return $this->accessId;
    }

    public function setAccessId(?string $accessId): self
    {
        $this->accessId = $accessId;

        return $this;
    }

    public function getCarId(): ?string
    {
        return $this->carId;
    }

    public function setCarId(?string $carId): self
    {
        $this->carId = $carId;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): self
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getAvailableProducts(): Collection
    {
        return $this->availableProducts;
    }

    public function addAvailableProduct(Product $availableProduct): self
    {
        if (!$this->availableProducts->contains($availableProduct)) {
            $this->availableProducts[] = $availableProduct;
        }

        return $this;
    }

    public function removeAvailableProduct(Product $availableProduct): self
    {
        if ($this->availableProducts->contains($availableProduct)) {
            $this->availableProducts->removeElement($availableProduct);
        }

        return $this;
    }

    /**
     * @return Collection|OrderNote[]
     */
    public function getOrderNotes(): Collection
    {
        return $this->orderNotes;
    }

    public function addOrderNote(OrderNote $orderNote): self
    {
        if (!$this->orderNotes->contains($orderNote)) {
            $this->orderNotes[] = $orderNote;
            $orderNote->setUser($this);
        }

        return $this;
    }

    public function removeOrderNote(OrderNote $orderNote): self
    {
        if ($this->orderNotes->contains($orderNote)) {
            $this->orderNotes->removeElement($orderNote);
            // set the owning side to null (unless already changed)
            if ($orderNote->getUser() === $this) {
                $orderNote->setUser(null);
            }
        }

        return $this;
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
            $deliveryAdress->setClient($this);
        }

        return $this;
    }

    public function removeDeliveryAdress(DeliveryAddress $deliveryAdress): self
    {
        if ($this->deliveryAddresses->contains($deliveryAdress)) {
            $this->deliveryAddresses->removeElement($deliveryAdress);
            // set the owning side to null (unless already changed)
            if ($deliveryAdress->getClient() === $this) {
                $deliveryAdress->setClient(null);
            }
        }

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
            $order->setClient($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getClient() === $this) {
                $order->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getDriverOrders(): Collection
    {
        return $this->driverOrders;
    }

    public function addDriverOrder(Order $driverOrder): self
    {
        if (!$this->driverOrders->contains($driverOrder)) {
            $this->driverOrders[] = $driverOrder;
            $driverOrder->setDriver($this);
        }

        return $this;
    }

    public function removeDriverOrder(Order $driverOrder): self
    {
        if ($this->driverOrders->contains($driverOrder)) {
            $this->driverOrders->removeElement($driverOrder);
            // set the owning side to null (unless already changed)
            if ($driverOrder->getDriver() === $this) {
                $driverOrder->setDriver(null);
            }
        }

        return $this;
    }

    public function getLastVisit(): ?\DateTimeInterface
    {
        return $this->lastVisit;
    }

    public function setLastVisit(?\DateTimeInterface $lastVisit): self
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getPlainRole(): ?string
    {
        return $this->plainRole;
    }

    public function setPlainRole(?string $plainRole): self
    {
        $this->plainRole = $plainRole;

        return $this;
    }

    /**
     * @return int
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("delivery_addresses")
     * @JMS\Groups({"client", "basicClient"})
     */
    public function getDeliveryAddressesCount()
    {
        return $this->getDeliveryAddresses()->count() ?? 0;
    }

    /**
     * @return int
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("orders")
     * @JMS\Groups({"client", "basicClient"})
     */
    public function getOrdersCount()
    {
        return $this->getOrders()->count() ?? 0;
    }

    /**
     * @return int
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("driver_orders")
     * @JMS\Groups({"driver"})
     */
    public function getDeliverOrdersCount()
    {
        return $this->getDriverOrders()->count() ?? 0;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): self
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages[] = $sentMessage;
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $sentMessage): self
    {
        if ($this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->removeElement($sentMessage);
            // set the owning side to null (unless already changed)
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getRecievedMessages(): Collection
    {
        return $this->recievedMessages;
    }

    public function addRecievedMessage(Message $recievedMessage): self
    {
        if (!$this->recievedMessages->contains($recievedMessage)) {
            $this->recievedMessages[] = $recievedMessage;
            $recievedMessage->setReciever($this);
        }

        return $this;
    }

    public function removeRecievedMessage(Message $recievedMessage): self
    {
        if ($this->recievedMessages->contains($recievedMessage)) {
            $this->recievedMessages->removeElement($recievedMessage);
            // set the owning side to null (unless already changed)
            if ($recievedMessage->getReciever() === $this) {
                $recievedMessage->setReciever(null);
            }
        }

        return $this;
    }
}

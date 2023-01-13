<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{

    const ORDER_STATUS = ['new', 'shipped', 'delivered'];
    CONST ORDER_STATUS_CODES = [
        'new' => 0,
        'shipped' => 1,
        'delivered' => 2
    ];
    CONST ORDER_CUSTOM_STATUS_CODES = [
        'new' => 0,
        'preparing' => 1,
        'shipped' => 2,
        'delivered' => 3
    ];
    CONST ORDER_DRIVER_CUSTOM_STATUS_CODES = [
        'new' => 0,
        'accepted' => 1,
        'shipped' => 2,
        'delivered' => 3
    ];
    const ORDER_PAYMENT_STATUS = ['paid', 'not_paid'];
    const ORDER_PAYMENT_METHODS = ['visa', 'master card', 'wallet'];
    const ORDER_ORIGIN = ['ios', 'android', 'dashboard', 'website'];

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client", "driver", "delivery"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client", "delivery"})
     */
    private $reference;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"client", "delivery"})
     * @JMS\Type("DateTime<'d-m-Y'>")
     */
    private $deliveryDate;

    /**
     * @ORM\ManyToOne(targetEntity=Slot::class, inversedBy="orders")
     * @JMS\Groups({"client", "delivery"})
     */
    private $deliverySlot;

    /**
     * @ORM\OneToMany(targetEntity=OrderDetail::class, mappedBy="parentOrder", cascade={"all"})
     * @JMS\Groups({"client", "delivery"})
     */
    private $orderDetails;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @JMS\Groups({"delivery"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @JMS\Groups({"deliveryShop"})
     */
    private $shop;

    /**
     * @ORM\ManyToOne(targetEntity=DeliveryAddress::class, inversedBy="orders")
     * @JMS\Groups({"client", "delivery"})
     */
    private $deliveryAddress;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client", "delivery"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $paymentStatus;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"client"})
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client"})
     */
    private $paymentMethod;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $orderOrigin;

    /**
     * @ORM\ManyToOne(targetEntity=DiscountVoucher::class, inversedBy="orders")
     * @JMS\Groups({"client"})
     */
    private $discountVoucher;

    /**
     * @ORM\Column(type="float")
     * @JMS\Groups({"client", "delivery"})
     */
    private $subtotalPrice;

    /**
     * @ORM\Column(type="float")
     * @JMS\Groups({"client"})
     */
    private $VAT;

    /**
     * @ORM\Column(type="float")
     * @JMS\Groups({"client"})
     */
    private $totalPrice;

    /**
     * @ORM\OneToMany(targetEntity=OrderNote::class, mappedBy="parentOrder", cascade={"all"})
     */
    private $notes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client"})
     */
    private $messageFrom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client"})
     */
    private $messageTo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"client"})
     */
    private $message;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"client"})
     */
    private $messageLink;

    /**
     * @ORM\OneToOne(targetEntity=MessageFile::class, inversedBy="parentOrder", cascade={"persist", "remove"})
     * @JMS\Groups({"client"})
     */
    private $messageFile;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="driverOrders")
     */
    private $driver;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Type("DateTime<'d-m-Y H:i'>")
     * @JMS\Groups({"client", "driver", "delivery"})
     */
    private $acceptedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveredAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Groups({"client", "driver", "delivery"})
     */
    private $hideSender = false;

    /**
     * @ORM\OneToMany(targetEntity=OrderImage::class, mappedBy="parentOrder")
     * @JMS\Groups({"client", "delivery"})
     */
    private $images;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentTransaction = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->notes = new ArrayCollection();

        $this->addOrderDetail(new OrderDetail());
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getDeliverySlot(): ?Slot
    {
        return $this->deliverySlot;
    }

    public function setDeliverySlot(?Slot $deliverySlot): self
    {
        $this->deliverySlot = $deliverySlot;

        return $this;
    }

    /**
     * @return Collection|OrderDetail[]
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetail $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails[] = $orderDetail;
            $orderDetail->setParentOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetail $orderDetail): self
    {
        if ($this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->removeElement($orderDetail);
            // set the owning side to null (unless already changed)
            if ($orderDetail->getParentOrder() === $this) {
                $orderDetail->setParentOrder(null);
            }
        }

        return $this;
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

    public function getShop(): ?User
    {
        return $this->shop;
    }

    public function setShop(?User $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getDeliveryAddress(): ?DeliveryAddress
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?DeliveryAddress $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getOrderOrigin(): ?string
    {
        return $this->orderOrigin;
    }

    public function setOrderOrigin(string $orderOrigin): self
    {
        $this->orderOrigin = $orderOrigin;

        return $this;
    }

    public function getDiscountVoucher(): ?DiscountVoucher
    {
        return $this->discountVoucher;
    }

    public function setDiscountVoucher(?DiscountVoucher $discountVoucher): self
    {
        $this->discountVoucher = $discountVoucher;

        return $this;
    }

    public function getSubtotalPrice(): ?float
    {
        return $this->subtotalPrice;
    }

    public function setSubtotalPrice(float $subtotalPrice): self
    {
        $this->subtotalPrice = $subtotalPrice;

        return $this;
    }

    public function getVAT(): ?float
    {
        return $this->VAT;
    }

    public function setVAT(float $VAT): self
    {
        $this->VAT = $VAT;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice($deliveryPrice): self
    {
        $this->totalPrice = $this->getSubtotalPrice() + ($this->getVAT() / 100 * $this->getSubtotalPrice()) + $deliveryPrice;

        return $this;
    }

    public function getVATAmount()
    {
        return $this->getVAT() / 100 * $this->getSubtotalPrice();

    }

    /**
     * @return Collection|OrderNote[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(OrderNote $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setParentOrder($this);
        }

        return $this;
    }

    public function removeNote(OrderNote $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getParentOrder() === $this) {
                $note->setParentOrder(null);
            }
        }

        return $this;
    }

    public function getMessageFrom(): ?string
    {
        return $this->messageFrom;
    }

    public function setMessageFrom(?string $messageFrom): self
    {
        $this->messageFrom = $messageFrom;

        return $this;
    }

    public function getMessageTo(): ?string
    {
        return $this->messageTo;
    }

    public function setMessageTo(?string $messageTo): self
    {
        $this->messageTo = $messageTo;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessageLink(): ?string
    {
        return $this->messageLink;
    }

    public function setMessageLink(?string $messageLink): self
    {
        $this->messageLink = $messageLink;

        return $this;
    }

    public function getMessageFile(): ?MessageFile
    {
        return $this->messageFile;
    }

    public function setMessageFile(?MessageFile $messageFile): self
    {
        $this->messageFile = $messageFile;
        $this->messageFile->setParentOrder($this);

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeInterface
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTimeInterface $acceptedAt): self
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $deliveredAt): self
    {
        $this->deliveredAt = $deliveredAt;

        return $this;
    }

    public function getHideSender(): ?bool
    {
        return $this->hideSender;
    }

    public function setHideSender(?bool $hideSender): self
    {
        $this->hideSender = $hideSender;

        return $this;
    }

    /**
     * @return mixed
     * @JMS\VirtualProperty()
     * @JMS\Groups({"client", "delivery"})
     */
    public function getStatusCode()
    {
        if (array_key_exists($this->getStatus(), self::ORDER_STATUS_CODES)) {
            return self::ORDER_STATUS_CODES[$this->getStatus()];
        }
        return null;
    }

    /**
     * @return mixed
     * @JMS\VirtualProperty()
     * @JMS\Groups({"client"})
     */
    public function getCustomStatusCode()
    {
        switch ($this->getStatusCode()) {
            case 0:
                if (is_null($this->getShop())) {
                    return self::ORDER_CUSTOM_STATUS_CODES['new'];
                    break;
                } else {
                    return self::ORDER_CUSTOM_STATUS_CODES['preparing'];
                    break;
                }
            case 1:
                return self::ORDER_CUSTOM_STATUS_CODES['shipped'];
                break;
            case 2:
                return self::ORDER_CUSTOM_STATUS_CODES['delivered'];
                break;
        }
        return null;
    }

    /**
     * @return mixed
     * @JMS\VirtualProperty()
     * @JMS\Groups({"delivery"})
     */
    public function getDriverCustomStatusCode()
    {
        switch ($this->getStatusCode()) {
            case 0:
                if (is_null($this->getDriver())) {
                    return self::ORDER_DRIVER_CUSTOM_STATUS_CODES['new'];
                    break;
                } else {
                    return self::ORDER_DRIVER_CUSTOM_STATUS_CODES['accepted'];
                    break;
                }
            case 1:
                return self::ORDER_DRIVER_CUSTOM_STATUS_CODES['shipped'];
                break;
            case 2:
                return self::ORDER_DRIVER_CUSTOM_STATUS_CODES['delivered'];
                break;
        }
        return null;
    }

    /**
     * @return Collection|OrderImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(OrderImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setParentOrder($this);
        }

        return $this;
    }

    public function removeImage(OrderImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getParentOrder() === $this) {
                $image->setParentOrder(null);
            }
        }

        return $this;
    }

    public function getPaymentTransaction(): ?string
    {
        return $this->paymentTransaction;
    }

    public function setPaymentTransaction(?string $paymentTransaction): self
    {
        $this->paymentTransaction = $paymentTransaction;

        return $this;
    }
}

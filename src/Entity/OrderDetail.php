<?php

namespace App\Entity;

use App\Repository\OrderDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrderDetailRepository::class)
 */
class OrderDetail
{
    Use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="orderDetails")
     * @Groups({"client", "delivery"})
     */
    private $product;

    /**
     * @ORM\Column(type="float")
     * @Groups({"client"})
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"client", "delivery"})
     */
    private $quantity = 1;


    /**
     * @var float
     * @Groups({"client"})
     */
    private $subTotal;

    /**
     * @var float
     * @Groups({"client"})
     */
    private $subTotalAfterDiscount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"client"})
     */
    private $discountable;

    /**
     * @ORM\Column(type="float")
     * @Groups({"client"})
     */
    private $discount;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderDetails")
     */
    private $parentOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSubTotal(): ?float
    {
        return $this->price * $this->quantity;
    }


    public function getDiscountable(): ?bool
    {
        return $this->discountable;
    }

    public function setDiscountable(?bool $discountable): self
    {
        $this->discountable = $discountable;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubTotalAfterDiscount(): float
    {
        if ($this->discountable) {
            return $this->getSubTotal() - ($this->getDiscount() / 100 * $this->getSubTotal());
        }
        return $this->getSubTotal();
    }

    public function getParentOrder(): ?Order
    {
        return $this->parentOrder;
    }

    public function setParentOrder(?Order $parentOrder): self
    {
        $this->parentOrder = $parentOrder;

        return $this;
    }

    public function getDiscountAmount()
    {
        return $this->getDiscount() / 100 * $this->getSubTotal();
    }
}

<?php

namespace App\Entity;

use App\Repository\ProductColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=ProductColorRepository::class)
 */
class ProductColor
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $colorAr;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="color")
     */
    private $products;


    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setColor($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getColor() === $this) {
                $product->setColor(null);
            }
        }

        return $this;
    }

    public function getColorAr(): ?string
    {
        return $this->colorAr;
    }

    public function setColorAr(string $colorAr): self
    {
        $this->colorAr = $colorAr;

        return $this;
    }
}

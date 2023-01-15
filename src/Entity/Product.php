<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use phpDocumentor\Reflection\Types\Mixed_;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{

    use TimestampableEntity;

    const PRODUCT_COLORS = [
        'white' => '#ffffff',
        'aqua' => '#72CEDD',
        'orange' => '#FA8171',
        'pink' => '#EE84B5',
        'purple' => '#8914AE'
    ];

    const PRODUCT_TYPES = ['bouquet', 'box', 'vase'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client", "delivery"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client", "delivery"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client", "delivery"})
     */
    private $nameAr;


    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=ProductColor::class, inversedBy="products")
     * @JMS\Groups({"client"})
     */
    private $color;

    /**
     * @var float
     */
    private $price;


    /**
     * @ORM\Column(type="text", length=255)
     * @JMS\Groups({"client"})
     */
    private $description;

    /**
     * @ORM\Column(type="text", length=255)
     * @JMS\Groups({"client"})
     */
    private $descriptionAr;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @JMS\Groups({"client"})
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductImage", mappedBy="product", cascade={"all"})
     * @JMS\Groups({"client", "delivery"})
     */
    private $images;


    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"client"})
     */
    private $SKU;

    /**
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @ORM\Column(type="float")
     */
    private $benefit;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"client"})
     */
    private $longDescription;

    /**
     * Visible on store field
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $visible = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $discountable = true;

    /**
     * @ORM\Column(type="integer")
     */
    private $views = 0;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="availableProducts")
     * @JMS\Groups({"client"})
     */
    private $users;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"client"})
     */
    private $longDescriptionAr;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"client"})
     */
    private $designedBy;

    /**
     * @var int|null
     * @JMS\Groups({"client"})
     * @JMS\SerializedName("count")
     */
    private $countFromServer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Groups({"client", "delivery"})
     */
    private $package = false;




    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getColor(): ?ProductColor
    {
        return $this->color;
    }

    public function setColor(?ProductColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->cost + $this->benefit;
    }

    /**
     * @return float
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("price")
     * @JMS\Groups({"client"})
     */
    public function getTotalPrice()
    {
        return $this->getPrice();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|ProductImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): self
    {
        if (!$this->images->contains($image) && !is_null($image->getImageFile())) {
            $this->images[] = $image;
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(ProductImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }


    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }


    public function getSKU(): ?string
    {
        return $this->SKU;
    }

    public function setSKU(string $SKU): self
    {
        $this->SKU = $SKU;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getBenefit(): ?float
    {
        return $this->benefit;
    }

    public function setBenefit(float $benefit): self
    {
        $this->benefit = $benefit;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): self
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): self
    {
        $this->visible = $visible;

        return $this;
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

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function __toString()
    {
        return $this->SKU . ' - ' . $this->name;
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
            $user->addAvailableProduct($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeAvailableProduct($this);
        }

        return $this;
    }

    public function getLongDescriptionAr(): ?string
    {
        return $this->longDescriptionAr;
    }

    public function setLongDescriptionAr(?string $longDescriptionAr): self
    {
        $this->longDescriptionAr = $longDescriptionAr;

        return $this;
    }

    public function getDesignedBy(): ?string
    {
        return $this->designedBy;
    }

    public function setDesignedBy(?string $designedBy): self
    {
        $this->designedBy = $designedBy;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCountFromServer(): ?int
    {
        return $this->countFromServer;
    }

    /**
     * @param int|string $countFromServer
     */
    public function setCountFromServer(?int $countFromServer): void
    {
        $this->countFromServer = $countFromServer;
    }

    public function getPackage(): ?bool
    {
        return $this->package;
    }

    public function setPackage(?bool $package): self
    {
        $this->package = $package;

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

    public function getDescriptionAr(): ?string
    {
        return $this->descriptionAr;
    }

    public function setDescriptionAr(string $descriptionAr): self
    {
        $this->descriptionAr = $descriptionAr;

        return $this;
    }

}

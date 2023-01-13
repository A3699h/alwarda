<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Fresh\VichUploaderSerializationBundle\Annotation as Fresh;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @Vich\Uploadable
 * @Fresh\VichSerializableClass
 */
class Category
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({"client", "category"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"category"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"category"})
     */
    private $nameAr;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     * @JMS\SerializedName("icon_path")
     *
     * @Fresh\VichSerializableField("iconFile")
     * @JMS\Groups({"category"})
     */
    private $icon;

    /**
     * @Vich\UploadableField(mapping="categories_icons", fileNameProperty="icon")
     * @var File
     */
    private $iconFile;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="category")
     * @JMS\Groups({"category"})
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setIconFile(File $icon = null)
    {
        $this->iconFile = $icon;

        if ($icon) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getIconFile()
    {
        return $this->iconFile;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
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
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
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
}

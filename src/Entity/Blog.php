<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Fresh\VichUploaderSerializationBundle\Annotation as Fresh;
use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=BlogRepository::class)
 * @Vich\Uploadable
 * @Fresh\VichSerializableClass
 */
class Blog
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"title","id"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titleAr;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descriptionAr;

    /**
     * @ORM\OneToMany(targetEntity=BlogSection::class, mappedBy="blog", cascade={"all"})
     */
    private $sections;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $published = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     *
     * @JMS\Expose
     * @JMS\SerializedName("image_path")
     *
     * @Fresh\VichSerializableField("imageFile")
     * @JMS\Groups({"client", "delivery"})
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="blog_images", fileNameProperty="image")
     * @var File
     */
    private $imageFile;


    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }


    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    /**
     * @return Collection|BlogSection[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(BlogSection $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setBlog($this);
        }

        return $this;
    }

    public function removeSection(BlogSection $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getBlog() === $this) {
                $section->setBlog(null);
            }
        }

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function getTitleAr(): ?string
    {
        return $this->titleAr;
    }

    public function setTitleAr(string $titleAr): self
    {
        $this->titleAr = $titleAr;

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

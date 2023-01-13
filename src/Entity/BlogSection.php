<?php

namespace App\Entity;

use App\Repository\BlogSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Fresh\VichUploaderSerializationBundle\Annotation as Fresh;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=BlogSectionRepository::class)
 * @Vich\Uploadable
 * @Fresh\VichSerializableClass
 */
class BlogSection
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Blog::class, inversedBy="sections")
     */
    private $blog;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

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

    /**
     * @ORM\Column(type="text")
     */
    private $contentAr;


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

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): self
    {
        $this->blog = $blog;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContentAr(): ?string
    {
        return $this->contentAr;
    }

    public function setContentAr(string $contentAr): self
    {
        $this->contentAr = $contentAr;

        return $this;
    }
}

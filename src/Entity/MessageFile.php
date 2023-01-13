<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Fresh\VichUploaderSerializationBundle\Annotation as Fresh;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageFileRepository")
 * @Vich\Uploadable
 * @Fresh\VichSerializableClass
 */
class MessageFile
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     *
     * @JMS\Expose
     * @JMS\SerializedName("file_path")
     *
     * @Fresh\VichSerializableField("fileFile")
     * @JMS\Groups({"client"})
     */
    private $file;

    /**
     * @Vich\UploadableField(mapping="client_files", fileNameProperty="file")
     * @var File
     */
    private $fileFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $viewedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $qrCode;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, mappedBy="messageFile", cascade={"persist", "remove"})
     */
    private $parentOrder;


    public function setFileFile(File $file = null)
    {
        $this->fileFile = $file;

        if ($file) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getFileFile()
    {
        return $this->fileFile;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViewedAt(): ?\DateTimeInterface
    {
        return $this->viewedAt;
    }

    public function setViewedAt(?\DateTimeInterface $viewedAt): self
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(string $qrCode): string
    {
        if (is_null($this->qrCode) || $this->qrCode == '') {
            $this->qrCode = $qrCode;
        }

        return $this->getQrCode();
    }

    public function getParentOrder(): ?Order
    {
        return $this->parentOrder;
    }

    public function setParentOrder(?Order $parentOrder): self
    {
        $this->parentOrder = $parentOrder;

        // set (or unset) the owning side of the relation if necessary
        $newMessageFile = null === $parentOrder ? null : $this;
        if ($parentOrder->getMessageFile() !== $newMessageFile) {
            $parentOrder->setMessageFile($newMessageFile);
        }

        return $this;
    }
}

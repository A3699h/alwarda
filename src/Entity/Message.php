<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{

    use TimestampableEntity;


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sentMessages")
     * @JMS\Groups({"default"})
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recievedMessages")
     * @JMS\Groups({"default"})
     */
    private $reciever;

    /**
     * @ORM\Column(type="text")
     * @JMS\Groups({"default"})
     */
    private $message;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @JMS\Groups({"default"})
     */
    private $new;

    /**
     * @return \DateTime
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     * @JMS\Type("DateTime<'d-m-Y H:i'>")
     */
    public function getDateTime()
    {
        return $this->getCreatedAt();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReciever(): ?User
    {
        return $this->reciever;
    }

    public function setReciever(?User $reciever): self
    {
        $this->reciever = $reciever;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getNew(): ?bool
    {
        return $this->new;
    }

    public function setNew(?bool $new): self
    {
        $this->new = $new;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\FAQRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FAQRepository::class)
 */
class FAQ
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $question;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $questionAr;

    /**
     * @ORM\Column(type="text")
     */
    private $answer;
    /**
     * @ORM\Column(type="text")
     */
    private $answerAr;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnswerAr()
    {
        return $this->answerAr;
    }

    /**
     * @param mixed $answerAr
     */
    public function setAnswerAr($answerAr): self
    {
        $this->answerAr = $answerAr;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuestionAr()
    {
        return $this->questionAr;
    }

    /**
     * @param mixed $questionAr
     */
    public function setQuestionAr($questionAr): self
    {
        $this->questionAr = $questionAr;
        return $this;
    }
}

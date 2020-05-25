<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryCommand
 *
 * @ORM\Table(name="history_command", indexes={@ORM\Index(name="history_command_id_command_index", columns={"id_command"})})
 * @ORM\Entity(repositoryClass="App\Repository\HistoryCommandRepository")
 */
class HistoryCommand
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="id_payment", type="string", length=255, nullable=false)
     */
    private $idPayment;

    /**
     * @var string|null
     *
     * @ORM\Column(name="other_id", type="string", length=255, nullable=true)
     */
    private $otherId;

    /**
     * @var bool
     *
     * @ORM\Column(name="activity", type="boolean", nullable=false, options={"default"="1"})
     */
    private $activity = true;

    /**
     * @var \Commande
     *
     * @ORM\ManyToOne(targetEntity="Commande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_command", referencedColumnName="id")
     * })
     */
    private $idCommand;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPayment(): ?string
    {
        return $this->idPayment;
    }

    public function setIdPayment(string $idPayment): self
    {
        $this->idPayment = $idPayment;

        return $this;
    }

    public function getOtherId(): ?string
    {
        return $this->otherId;
    }

    public function setOtherId(?string $otherId): self
    {
        $this->otherId = $otherId;

        return $this;
    }

    public function getActivity(): ?bool
    {
        return $this->activity;
    }

    public function setActivity(bool $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getIdCommand(): ?Commande
    {
        return $this->idCommand;
    }

    public function setIdCommand(?Commande $idCommand): self
    {
        $this->idCommand = $idCommand;

        return $this;
    }


}

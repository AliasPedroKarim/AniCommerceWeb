<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reduction
 *
 * @ORM\Table(name="reduction", indexes={@ORM\Index(name="id_type_reduction", columns={"id_type_reduction"})})
 * @ORM\Entity(repositoryClass="App\Repository\ReductionRepository")
 */
class Reduction
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
     * @var string|null
     *
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime", nullable=false)
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=false)
     */
    private $dateFin;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="actif", type="boolean", nullable=true)
     */
    private $actif;

    /**
     * @var string|null
     *
     * @ORM\Column(name="valeur", type="decimal", precision=15, scale=2, nullable=true)
     */
    private $valeur;

    /**
     * @var \TypeReduction
     *
     * @ORM\ManyToOne(targetEntity="TypeReduction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type_reduction", referencedColumnName="id")
     * })
     */
    private $idTypeReduction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getValeur(): ?string
    {
        return $this->valeur;
    }

    public function setValeur(?string $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getIdTypeReduction(): ?TypeReduction
    {
        return $this->idTypeReduction;
    }

    public function setIdTypeReduction(?TypeReduction $idTypeReduction): self
    {
        $this->idTypeReduction = $idTypeReduction;

        return $this;
    }


}

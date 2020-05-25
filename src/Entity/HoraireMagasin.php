<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HoraireMagasin
 *
 * @ORM\Table(name="horaire_magasin", indexes={@ORM\Index(name="horaire_magasin_id_magasin_index", columns={"id_magasin"})})
 * @ORM\Entity(repositoryClass="App\Repository\HoraireMagasinRepository") 
 */
class HoraireMagasin
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
     * @ORM\Column(name="jour", type="string", length=50, nullable=false)
     */
    private $jour;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="h_ouverture_matin", type="time", nullable=true)
     */
    private $hOuvertureMatin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="h_fermeture_matin", type="time", nullable=true)
     */
    private $hFermetureMatin;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="h_ouverture_midi", type="time", nullable=true)
     */
    private $hOuvertureMidi;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="h_fermeture_midi", type="time", nullable=true)
     */
    private $hFermetureMidi;

    /**
     * @var \Magasin
     *
     * @ORM\ManyToOne(targetEntity="Magasin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_magasin", referencedColumnName="id")
     * })
     */
    private $idMagasin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour()
    {
        return $this->jour;
    }

    public function setJour($jour): self
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHOuvertureMatin(): ?\DateTimeInterface
    {
        return $this->hOuvertureMatin;
    }

    public function setHOuvertureMatin(?\DateTimeInterface $hOuvertureMatin): self
    {
        $this->hOuvertureMatin = $hOuvertureMatin;

        return $this;
    }

    public function getHFermetureMatin(): ?\DateTimeInterface
    {
        return $this->hFermetureMatin;
    }

    public function setHFermetureMatin(?\DateTimeInterface $hFermetureMatin): self
    {
        $this->hFermetureMatin = $hFermetureMatin;

        return $this;
    }

    public function getHOuvertureMidi(): ?\DateTimeInterface
    {
        return $this->hOuvertureMidi;
    }

    public function setHOuvertureMidi(?\DateTimeInterface $hOuvertureMidi): self
    {
        $this->hOuvertureMidi = $hOuvertureMidi;

        return $this;
    }

    public function getHFermetureMidi(): ?\DateTimeInterface
    {
        return $this->hFermetureMidi;
    }

    public function setHFermetureMidi(?\DateTimeInterface $hFermetureMidi): self
    {
        $this->hFermetureMidi = $hFermetureMidi;

        return $this;
    }

    public function getIdMagasin(): ?Magasin
    {
        return $this->idMagasin;
    }

    public function setIdMagasin(?Magasin $idMagasin): self
    {
        $this->idMagasin = $idMagasin;

        return $this;
    }


}

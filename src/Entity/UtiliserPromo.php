<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UtiliserPromo
 *
 * @ORM\Table(name="utiliser_promo", indexes={@ORM\Index(name="id_commande", columns={"id_commande"}), @ORM\Index(name="id_reduction", columns={"id_reduction"})})
 * @ORM\Entity(repositoryClass="App\Repository\UtiliserPromoRepository")
 */
class UtiliserPromo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var \Commande
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Commande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_commande", referencedColumnName="id")
     * })
     */
    private $idCommande;

    /**
     * @var \Reduction
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Reduction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_reduction", referencedColumnName="id")
     * })
     */
    private $idReduction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCommande(): ?Commande
    {
        return $this->idCommande;
    }

    public function setIdCommande(?Commande $idCommande): self
    {
        $this->idCommande = $idCommande;

        return $this;
    }

    public function getIdReduction(): ?Reduction
    {
        return $this->idReduction;
    }

    public function setIdReduction(?Reduction $idReduction): self
    {
        $this->idReduction = $idReduction;

        return $this;
    }


}

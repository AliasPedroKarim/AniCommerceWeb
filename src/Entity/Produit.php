<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Produit
 *
 * @ORM\Table(name="produit", indexes={@ORM\Index(name="id_magasin", columns={"id_magasin"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProduitRepository")
 */
class Produit
{
    public function __construct() {
        $this->associerCategories = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

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
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="prix_ht", type="decimal", precision=15, scale=2, nullable=true)
     */
    private $prixHt;

    /**
     * @var int|null
     *
     * @ORM\Column(name="stock", type="integer", nullable=true)
     */
    private $stock;

    /**
     * @var \Magasin
     *
     * @ORM\ManyToOne(targetEntity="Magasin")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_magasin", referencedColumnName="id")
     * })
     */
    private $idMagasin;

    /**
     * @var AssocierCategorie
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AssocierCategorie",mappedBy="idProduit")
     */
    private $associerCategories;

    /**
     * @return Collection|AssocierCategorie[]
     */
    public function getAssocierCategories(): Collection
    {
        return $this->associerCategories;
    }

    /**
     * @param ArrayCollection $associerCategories
     */
    public function setAssocierCategories(ArrayCollection $associerCategories): void
    {
        $this->associerCategories = $associerCategories;
    }

    /**
     * @var Presenter
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Presenter",mappedBy="idProduit")
     */
    private $images;

    /**
     * @return Collection|Presenter[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection $images
     */
    public function setImages(ArrayCollection $images): void
    {
        $this->images = $images;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrixHt(): ?string
    {
        return $this->prixHt;
    }

    public function setPrixHt(?string $prixHt): self
    {
        $this->prixHt = $prixHt;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

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

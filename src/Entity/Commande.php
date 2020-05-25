<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FontLib\TrueType\Collection;

/**
 * Commande
 *
 * @ORM\Table(name="commande", indexes={@ORM\Index(name="id_utilisateur", columns={"id_utilisateur"}), @ORM\Index(name="id_adresse", columns={"id_adresse"})})
 * @ORM\Entity(repositoryClass="App\Repository\CommandeRepository")
 */
class Commande
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_cde", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateCde = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_livraison", type="date", nullable=true)
     */
    private $dateLivraison;

    /**
     * @var string|null
     *
     * @ORM\Column(name="chemin_facture", type="string", length=255, nullable=true)
     */
    private $cheminFacture;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="panier", type="boolean", nullable=true)
     */
    private $panier;

    /**
     * @var string|null
     *
     * @ORM\Column(name="methode_paiement", type="string", length=255, nullable=true)
     */
    private $methodePaiement;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
    private $idUtilisateur;

    /**
     * @var \Adresse
     *
     * @ORM\ManyToOne(targetEntity="Adresse")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_adresse", referencedColumnName="id")
     * })
     */
    private $idAdresse;

    /**
     * @var LigneCommande[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LigneCommande",mappedBy="idCommande")
     */
    private $ligneCommandes;

    public function __construct() {

        $this->ligneCommandes = new ArrayCollection();

    }

    /**
     * @return LigneCommande[]|Collection
     */
    public function getLigneCommandes()
    {
        return $this->ligneCommandes;
    }

    /**
     * @param LigneCommande[]|Collection $ligneCommandes
     */
    public function setLigneCommandes($ligneCommandes): void
    {
        $this->ligneCommandes = $ligneCommandes;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCde(): ?\DateTimeInterface
    {
        return $this->dateCde;
    }

    public function setDateCde(\DateTimeInterface $dateCde): self
    {
        $this->dateCde = $dateCde;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getCheminFacture(): ?string
    {
        return $this->cheminFacture;
    }

    public function setCheminFacture(?string $cheminFacture): self
    {
        $this->cheminFacture = $cheminFacture;

        return $this;
    }

    public function getPanier(): ?bool
    {
        return $this->panier;
    }

    public function setPanier(?bool $panier): self
    {
        $this->panier = $panier;

        return $this;
    }

    public function getMethodePaiement(): ?string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(?string $methodePaiement): self
    {
        $this->methodePaiement = $methodePaiement;

        return $this;
    }

    public function getIdUtilisateur(): ?Utilisateur
    {
        return $this->idUtilisateur;
    }

    public function setIdUtilisateur(?Utilisateur $idUtilisateur): self
    {
        $this->idUtilisateur = $idUtilisateur;

        return $this;
    }

    public function getIdAdresse(): ?Adresse
    {
        return $this->idAdresse;
    }

    public function setIdAdresse(?Adresse $idAdresse): self
    {
        $this->idAdresse = $idAdresse;

        return $this;
    }


}

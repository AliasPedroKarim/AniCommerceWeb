<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UtilisateurType
 *
 * @ORM\Table(name="utilisateur_type", indexes={@ORM\Index(name="table_name_utilisateur_id_fk", columns={"id_utilisateur"}), @ORM\Index(name="utilisateur_type_type_compte_id_fk", columns={"id_type_compte"})})
 * @ORM\Entity(repositoryClass="App\Repository\UtilisateurTypeRepository")
 */
class UtilisateurType
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
     * @var int|null
     *
     * @ORM\Column(name="identificateur", type="string", nullable=true)
     */
    private $identificateur;

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
     * @var \TypeCompte
     *
     * @ORM\ManyToOne(targetEntity="TypeCompte")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type_compte", referencedColumnName="id")
     * })
     */
    private $idTypeCompte;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentificateur(): ?string
    {
        return $this->identificateur;
    }

    public function setIdentificateur(?string $identificateur): self
    {
        $this->identificateur = $identificateur;

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

    public function getIdTypeCompte(): ?TypeCompte
    {
        return $this->idTypeCompte;
    }

    public function setIdTypeCompte(?TypeCompte $idTypeCompte): self
    {
        $this->idTypeCompte = $idTypeCompte;

        return $this;
    }


}

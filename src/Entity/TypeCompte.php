<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeCompte
 *
 * @ORM\Table(name="type_compte")
 * @ORM\Entity(repositoryClass="App\Repository\TypeCompteRepository")
 */
class TypeCompte
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
     * @var int
     *
     * @ORM\Column(name="libelle", type="string", nullable=false)
     */
    private $libelle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }


}

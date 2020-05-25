<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Image
 *
 * @ORM\Table(name="image", indexes={@ORM\Index(name="id_type_image", columns={"id_type_image"})})
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
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
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(name="chemin_image", type="string", length=255, nullable=true)
     */
    private $cheminImage;

    /**
     * @var \TypeImage
     *
     * @ORM\ManyToOne(targetEntity="TypeImage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type_image", referencedColumnName="id")
     * })
     */
    private $idTypeImage;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCheminImage(): ?string
    {
        return $this->cheminImage;
    }

    public function setCheminImage(?string $cheminImage): self
    {
        $this->cheminImage = $cheminImage;

        return $this;
    }

    public function getIdTypeImage(): ?TypeImage
    {
        return $this->idTypeImage;
    }

    public function setIdTypeImage(?TypeImage $idTypeImage): self
    {
        $this->idTypeImage = $idTypeImage;

        return $this;
    }


}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * SeatingType
 *
 * @ORM\Table(name="seating_type")
 * @ORM\Entity(repositoryClass="App\Repository\SeatingTypeRepository")
 */
class SeatingType
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     * @Groups({"read_coaster"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=false)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $displayHeatmap = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lines;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $columns;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return SeatingType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return SeatingType
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function getDisplayHeatmap(): ?bool
    {
        return $this->displayHeatmap;
    }

    public function setDisplayHeatmap(bool $displayHeatmap): self
    {
        $this->displayHeatmap = $displayHeatmap;

        return $this;
    }

    public function getLines(): ?int
    {
        return $this->lines;
    }

    public function setLines(?int $lines): self
    {
        $this->lines = $lines;

        return $this;
    }

    public function getColumns(): ?int
    {
        return $this->columns;
    }

    public function setColumns(?int $columns): self
    {
        $this->columns = $columns;

        return $this;
    }
}

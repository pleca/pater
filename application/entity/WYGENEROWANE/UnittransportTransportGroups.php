<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnittransportTransportGroups
 *
 * @ORM\Table(name="unitTransport_transport_groups")
 * @ORM\Entity
 */
class UnittransportTransportGroups
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_advertaising_material", type="boolean", nullable=false)
     */
    private $isAdvertaisingMaterial;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_excluded_from_free_delivery", type="integer", nullable=false)
     */
    private $isExcludedFromFreeDelivery;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isAdvertaisingMaterial()
    {
        return $this->isAdvertaisingMaterial;
    }

    /**
     * @param bool $isAdvertaisingMaterial
     */
    public function setIsAdvertaisingMaterial($isAdvertaisingMaterial)
    {
        $this->isAdvertaisingMaterial = $isAdvertaisingMaterial;
    }

    /**
     * @return int
     */
    public function getisExcludedFromFreeDelivery()
    {
        return $this->isExcludedFromFreeDelivery;
    }

    /**
     * @param int $isExcludedFromFreeDelivery
     */
    public function setIsExcludedFromFreeDelivery($isExcludedFromFreeDelivery)
    {
        $this->isExcludedFromFreeDelivery = $isExcludedFromFreeDelivery;
    }


}


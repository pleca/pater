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


}


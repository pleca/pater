<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportType
 *
 * @ORM\Table(name="transport_type")
 * @ORM\Entity
 */
class TransportType
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;


}


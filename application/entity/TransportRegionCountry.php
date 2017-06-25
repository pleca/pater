<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportRegionCountry
 *
 * @ORM\Table(name="transport_region_country")
 * @ORM\Entity
 */
class TransportRegionCountry
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
     * @var integer
     *
     * @ORM\Column(name="region_id", type="integer", nullable=false)
     */
    private $regionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="country_id", type="integer", nullable=false)
     */
    private $countryId;


}


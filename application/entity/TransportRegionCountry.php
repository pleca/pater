<?php
//ALTER TABLE transport_region_country CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE region_id region_id INT NOT NULL, CHANGE country_id country_id INT NOT NULL;

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


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
     * @return int
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param int $regionId
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;
    }

    /**
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }


}


<?php
//ALTER TABLE transport_region_service CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE region_id region_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportRegionService
 *
 * @ORM\Table(name="transport_region_service")
 * @ORM\Entity
 */
class TransportRegionService
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
     * @ORM\Column(name="service_id", type="integer", nullable=false)
     */
    private $serviceId;

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
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }


}


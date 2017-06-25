<?php



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


}


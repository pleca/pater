<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * TransportRegionPostcode
 *
 * @ORM\Table(name="transport_region_postcode")
 * @ORM\Entity
 */
class TransportRegionPostcode
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
     * @var string
     *
     * @ORM\Column(name="post1", type="string", length=10, nullable=false)
     */
    private $post1;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;


}


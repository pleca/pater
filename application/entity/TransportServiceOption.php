<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * TransportServiceOption
 *
 * @ORM\Table(name="transport_service_option")
 * @ORM\Entity
 */
class TransportServiceOption
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
     * @var string
     *
     * @ORM\Column(name="weight_from", type="decimal", precision=7, scale=0, nullable=true)
     */
    private $weightFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="weight_to", type="decimal", precision=7, scale=0, nullable=true)
     */
    private $weightTo;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_id", type="integer", nullable=false)
     */
    private $taxId;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_time", type="string", length=100, nullable=false)
     */
    private $deliveryTime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;


}


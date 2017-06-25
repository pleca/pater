<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * TransportCountry
 *
 * @ORM\Table(name="transport_country")
 * @ORM\Entity
 */
class TransportCountry
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=false)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;


}


<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity
 */
class Payment
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
     * @ORM\Column(name="name_url", type="text", length=255, nullable=false)
     */
    private $nameUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="order", type="boolean", nullable=false)
     */
    private $order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;


}


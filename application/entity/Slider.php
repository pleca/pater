<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Slider
 *
 * @ORM\Table(name="slider")
 * @ORM\Entity
 */
class Slider
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
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    private $target;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;

    /**
     * @var string
     *
     * @ORM\Column(name="active", type="string", nullable=false)
     */
    private $active;


}


<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Languages
 *
 * @ORM\Table(name="languages")
 * @ORM\Entity
 */
class Languages
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
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="default", type="boolean", nullable=false)
     */
    private $default;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active_front", type="boolean", nullable=false)
     */
    private $activeFront;


}


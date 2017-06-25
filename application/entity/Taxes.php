<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Taxes
 *
 * @ORM\Table(name="taxes")
 * @ORM\Entity
 */
class Taxes
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
     * @ORM\Column(name="value", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $value;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="smallint", nullable=true)
     */
    private $position;


}


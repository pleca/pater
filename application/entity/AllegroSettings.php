<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * AllegroSettings
 *
 * @ORM\Table(name="allegro_settings", uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"})})
 * @ORM\Entity
 */
class AllegroSettings
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
     * @ORM\Column(name="value", type="string", length=100, nullable=false)
     */
    private $value;


}


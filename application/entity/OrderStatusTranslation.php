<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OrderStatusTranslation
 *
 * @ORM\Table(name="order_status_translation", indexes={@ORM\Index(name="translatable_id", columns={"translatable_id"})})
 * @ORM\Entity
 */
class OrderStatusTranslation
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
     * @ORM\Column(name="translatable_id", type="integer", nullable=false)
     */
    private $translatableId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", nullable=false)
     */
    private $locale;


}


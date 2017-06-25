<?php
//ALTER TABLE product_status CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE `order` `order` TINYINT(1) NOT NULL;

//namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductStatus
 *
 * @ORM\Table(name="product_status")
 * @ORM\Entity
 */
class ProductStatus
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
     * @ORM\Column(name="color", type="string", length=10, nullable=false)
     */
    private $color;

    /**
     * @var boolean
     *
     * @ORM\Column(name="order", type="boolean", nullable=false)
     */
    private $order;


    //dodaję deklarację bo wywala error Uncaught exception: Call to undefined method ProductStatus::getAll()
    //bo jest jakiś konflikt z klasą z models/
    public function getAll(){}

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return bool
     */
    public function isOrder()
    {
        return $this->order;
    }

    /**
     * @param bool $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }


}


<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShoppingThresholds
 *
 * @ORM\Table(name="shopping_thresholds")
 * @ORM\Entity
 */
class ShoppingThresholds
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
     * @var string
     *
     * @ORM\Column(name="discount", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $discount;

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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param string $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }


}


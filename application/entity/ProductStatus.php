<?php

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
}

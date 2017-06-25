<?php
//ALTER TABLE basket CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE customer_id customer_id INT NOT NULL, CHANGE product_id product_id INT NOT NULL, CHANGE variation_id variation_id INT NOT NULL, CHANGE qty qty INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Basket
 *
 * @ORM\Table(name="basket")
 * @ORM\Entity
 */
class Basket
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=32, nullable=false)
     */
    private $sessionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="product_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $productId;

    /**
     * @var integer
     *
     * @ORM\Column(name="variation_id", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $variationId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="desc", type="string", length=250, nullable=false)
     */
    private $desc;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $tax;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", precision=5, scale=0, nullable=false)
     */
    private $weight;

    /**
     * @var integer
     *
     * @ORM\Column(name="qty", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $qty;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_add", type="datetime", nullable=false)
     */
    private $timeAdd;


}


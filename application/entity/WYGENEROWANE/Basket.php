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
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param int $variationId
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param string $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return \DateTime
     */
    public function getTimeAdd()
    {
        return $this->timeAdd;
    }

    /**
     * @param \DateTime $timeAdd
     */
    public function setTimeAdd($timeAdd)
    {
        $this->timeAdd = $timeAdd;
    }


}


<?php
//ALTER TABLE product_manufacturer CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE status_id status_id INT NOT NULL, CHANGE popular popular TINYINT(1) NOT NULL, CHANGE `order` `order` INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductManufacturer
 *
 * @ORM\Table(name="product_manufacturer")
 * @ORM\Entity
 */
class ProductManufacturer
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
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_url", type="text", length=255, nullable=false)
     */
    private $nameUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=100, nullable=false)
     */
    private $file;

    /**
     * @var boolean
     *
     * @ORM\Column(name="popular", type="boolean", nullable=false)
     */
    private $popular;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;


}


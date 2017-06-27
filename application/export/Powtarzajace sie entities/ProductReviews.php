<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductReviews
 *
 * @ORM\Table(name="product_reviews")
 * @ORM\Entity
 */
class ProductReviews
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
     * @ORM\Column(name="productId", type="integer", nullable=false)
     */
    private $productid;

    /**
     * @var integer
     *
     * @ORM\Column(name="customerId", type="integer", nullable=false)
     */
    private $customerid;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=100, nullable=false)
     */
    private $author;

    /**
     * @var integer
     *
     * @ORM\Column(name="reviewValue", type="smallint", nullable=false)
     */
    private $reviewvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="commentTitle", type="string", length=100, nullable=false)
     */
    private $commenttitle;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=false)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datePublished", type="datetime", nullable=true)
     */
    private $datepublished;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = '0';


}


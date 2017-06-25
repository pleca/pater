<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderAddress
 *
 * @ORM\Table(name="order_address")
 * @ORM\Entity
 */
class OrderAddress
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
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="string", nullable=false)
     */
    private $model;

    /**
     * @var boolean
     *
     * @ORM\Column(name="type", type="boolean", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=100, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="nip", type="string", length=16, nullable=false)
     */
    private $nip;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="string", length=100, nullable=false)
     */
    private $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=100, nullable=false)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="address3", type="string", length=100, nullable=false)
     */
    private $address3;

    /**
     * @var string
     *
     * @ORM\Column(name="post_code", type="string", length=16, nullable=false)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=100, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string", length=100, nullable=false)
     */
    private $province;

    /**
     * @var boolean
     *
     * @ORM\Column(name="country", type="boolean", nullable=false)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=false)
     */
    private $phone;

    /**
     * @var boolean
     *
     * @ORM\Column(name="shipping_type", type="boolean", nullable=false)
     */
    private $shippingType;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_company_name", type="string", length=100, nullable=false)
     */
    private $shippingCompanyName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_nip", type="string", length=16, nullable=false)
     */
    private $shippingNip;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_first_name", type="string", length=100, nullable=false)
     */
    private $shippingFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_last_name", type="string", length=100, nullable=false)
     */
    private $shippingLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address1", type="string", length=100, nullable=false)
     */
    private $shippingAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address2", type="string", length=100, nullable=false)
     */
    private $shippingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address3", type="string", length=100, nullable=false)
     */
    private $shippingAddress3;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_post_code", type="string", length=16, nullable=false)
     */
    private $shippingPostCode;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_city", type="string", length=100, nullable=false)
     */
    private $shippingCity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="shipping_country", type="boolean", nullable=false)
     */
    private $shippingCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_phone", type="string", length=16, nullable=false)
     */
    private $shippingPhone;


}


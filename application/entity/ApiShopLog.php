<?php
//ALTER TABLE api_shop_log CHANGE date_add date_add DATETIME NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApiShopLog
 *
 * @ORM\Table(name="api_shop_log")
 * @ORM\Entity
 */
class ApiShopLog
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
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=20, nullable=false)
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="text", length=65535, nullable=false)
     */
    private $params;

    /**
     * @var string
     *
     * @ORM\Column(name="fields", type="text", length=65535, nullable=false)
     */
    private $fields;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="text", length=65535, nullable=false)
     */
    private $result;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20, nullable=false)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd = 'CURRENT_TIMESTAMP';


}


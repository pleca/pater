<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderLogs
 *
 * @ORM\Table(name="order_logs")
 * @ORM\Entity
 */
class OrderLogs
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
     * @ORM\Column(name="orderId", type="integer", nullable=false)
     */
    private $orderid;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=100, nullable=false)
     */
    private $action;

    /**
     * @var array
     *
     * @ORM\Column(name="params", type="json_array", nullable=true)
     */
    private $params;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;


}


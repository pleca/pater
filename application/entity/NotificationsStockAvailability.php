<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationsStockAvailability
 *
 * @ORM\Table(name="notifications_stock_availability")
 * @ORM\Entity
 */
class NotificationsStockAvailability
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
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="variation_id", type="integer", nullable=false)
     */
    private $variationId;


}


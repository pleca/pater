<?php
//ALTER TABLE `order` CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE customer_id customer_id INT NOT NULL, CHANGE lang_id lang_id INT NOT NULL, CHANGE price price NUMERIC(10, 4) NOT NULL, CHANGE discount discount NUMERIC(5, 0) NOT NULL, CHANGE phrase_id phrase_id INT NOT NULL, CHANGE payment_id payment_id TINYINT(1) NOT NULL, CHANGE status_id status_id TINYINT(1) NOT NULL, CHANGE time_payment time_payment DATETIME NOT NULL, CHANGE time_complete time_complete DATETIME NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Order
 *
 * @ORM\Table(name="order")
 * @ORM\Entity
 */
class Order
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
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="lang_id", type="integer", nullable=false)
     */
    private $langId;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="decimal", precision=5, scale=0, nullable=false)
     */
    private $discount;

    /**
     * @var integer
     *
     * @ORM\Column(name="phrase_id", type="integer", nullable=false)
     */
    private $phraseId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payment_id", type="boolean", nullable=false)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="weight", type="decimal", precision=5, scale=0, nullable=false)
     */
    private $weight;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="comment_admin", type="text", length=65535, nullable=false)
     */
    private $commentAdmin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_add", type="datetime", nullable=false)
     */
    private $timeAdd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_payment", type="datetime", nullable=false)
     */
    private $timePayment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_complete", type="datetime", nullable=false)
     */
    private $timeComplete;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_transaction_id", type="string", length=100, nullable=false)
     */
    private $paypalTransactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="paypal_amount_fee", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $paypalAmountFee;

    /**
     * @var string
     *
     * @ORM\Column(name="tracking", type="string", length=100, nullable=false)
     */
    private $tracking;


}


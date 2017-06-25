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
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * @param int $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
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
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param string $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return int
     */
    public function getPhraseId()
    {
        return $this->phraseId;
    }

    /**
     * @param int $phraseId
     */
    public function setPhraseId($phraseId)
    {
        $this->phraseId = $phraseId;
    }

    /**
     * @return bool
     */
    public function isPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param bool $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
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
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getCommentAdmin()
    {
        return $this->commentAdmin;
    }

    /**
     * @param string $commentAdmin
     */
    public function setCommentAdmin($commentAdmin)
    {
        $this->commentAdmin = $commentAdmin;
    }

    /**
     * @return bool
     */
    public function isStatusId()
    {
        return $this->statusId;
    }

    /**
     * @param bool $statusId
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;
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

    /**
     * @return \DateTime
     */
    public function getTimePayment()
    {
        return $this->timePayment;
    }

    /**
     * @param \DateTime $timePayment
     */
    public function setTimePayment($timePayment)
    {
        $this->timePayment = $timePayment;
    }

    /**
     * @return \DateTime
     */
    public function getTimeComplete()
    {
        return $this->timeComplete;
    }

    /**
     * @param \DateTime $timeComplete
     */
    public function setTimeComplete($timeComplete)
    {
        $this->timeComplete = $timeComplete;
    }

    /**
     * @return string
     */
    public function getPaypalTransactionId()
    {
        return $this->paypalTransactionId;
    }

    /**
     * @param string $paypalTransactionId
     */
    public function setPaypalTransactionId($paypalTransactionId)
    {
        $this->paypalTransactionId = $paypalTransactionId;
    }

    /**
     * @return string
     */
    public function getPaypalAmountFee()
    {
        return $this->paypalAmountFee;
    }

    /**
     * @param string $paypalAmountFee
     */
    public function setPaypalAmountFee($paypalAmountFee)
    {
        $this->paypalAmountFee = $paypalAmountFee;
    }

    /**
     * @return string
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * @param string $tracking
     */
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;
    }


}


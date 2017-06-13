<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

require_once(MODEL_DIR . '/BasketModel.php');
use BasketModel;

require_once(CLASS_DIR . '/ValidatorTrait.php');
//require_once(SYS_DIR . '/core/Cms.php');
use Application\Classes\ValidatorTrait;


/**
 * @ORM\Entity 
 * @ORM\Table(name="order_logs")
 */
class OrderLog
{
	use ValidatorTrait;
	
    const ACTION_ORDER_CREATE = 'order_create';
    const ACTION_ORDER_CREATE_EMAIL_NOTIFICATION_SEND = 'order_create_email_notification_send';
    const ACTION_ORDER_CHOSEN_PAYMENT = 'chosen_payment';
    const ACTION_ORDER_PAYMENT = 'order_payment';
    const ACTION_ORDER_PAYMENT_EMAIL_NOTIFICATION_SEND = 'order_payment_email_notification_send';
    const ACTION_ORDER_MANUAL_STATUS_CHANGE = 'manual_status_change';
    const ACTION_ORDER_STATUS_EMAIL_NOTIFICATION_SEND = 'order_status_email_notification_send';
    const ACTION_ORDER_SENT_EMAIL_NOTIFICATION = 'order_sent_email_notification';
    const ACTION_ORDER_UPDATE_STATUS_BY_GA = 'order_update_status_by_ga';
    const ACTION_ORDER_UPDATE_TRACKING_BY_GA = 'order_update_tracking_by_ga';
    const ACTION_ORDER_EDIT = 'order_edit';
    const ACTION_ORDER_USE_PHRASE = 'order_use_phrase';
    const ACTION_ORDER_SHOW = 'show';
    
    const PAYMENT_TYPE_PAYPAL = 'paypal';
    const PAYMENT_TYPE_CREDIT_CARD = 'creditcard';
    const PAYMENT_TYPE_DOTPAY = 'dotpay';
    const PAYMENT_TYPE_ZA_POBRANIEM = 'zapobraniem';
    const PAYMENT_TYPE_ODBIOR_OSOBISTY = 'odbiorosobisty';
    
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer", options={"unsigned"=true}) 
	 * @ORM\GeneratedValue 
	 */
    private $id;
	    
    /**
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     */
    private $orderId;
    
    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100)
     */    
    private $action;
    
    /**
     * @ORM\Column(type="json_array", nullable=true)
     */	    
	private $params;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */	
	private $date;      
        
    public function __construct()
    {
        $this->date = new \DateTime();
    }    
	
    public static function getActions() 
    {
        $oClass = new \ReflectionClass('Application\Entity\OrderLog');
        $constants = $oClass->getConstants();

        $array = array();

        foreach ($constants as $constant => $value) {
            $pos = strpos($constant, 'ACTION');

            if ($pos !== false) {
                $array[$value] = $value;
            }
        }
        
        return $array;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }	
    
    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }	
    
    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }	
    
    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTime $date = null)
    {
        $this->date = $date;
    }    

    public function save($orderId, $action, array $params) {
        if (!$orderId || !$action) {
            return false;
        }
        
        $this->setOrderId($orderId);
        $this->setAction($action);
        $this->setParams($params);
        
        \Cms::$entityManager->persist($this);
        \Cms::$entityManager->flush();        
    }
    
    public function getTransport($option_id) {
        $basket = new BasketModel();
        $transport = $basket->getDelivery($option_id);
        
        return $transport;
    }
}
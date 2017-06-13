<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;

/*
 *	* * * * * command to be executed
 * 1 - Minute (0 - 59)
 * 2 - Hour (0 - 23)
 * 3 - Day of month (1 - 31)
 * 4 - Month (1 - 12)
 * 5 - Day of week (0 - 7) (Sunday=0 or 7)
 */


/**
 * @ORM\Entity 
 * @ORM\Table(name="crons")
 */
class Cron
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 * @ORM\GeneratedValue 
	 */
    private $id;
	
    /**
	 * @Assert\NotBlank(message = "cron.name.not_blank")
     * @ORM\Column(type="string", length=100)
     */	
	private $name;
	
    /**
     * @ORM\Column(type="text")
     */
    private $description;
	
	/**
	 * @ORM\Column(type="string", length=2) 
	 */
	private $minute;
	
	/**
	 * @ORM\Column(type="string", length=2)
	 */	
	private $hour;
	
	/**
	 * @ORM\Column(type="string", length=2)
	 */	
	private $dayOfMonth;
	
	/**
	 * @ORM\Column(type="string", length=2)
	 */	
	private $month;
	
	/**
	 * @ORM\Column(type="string", length=2)
	 */	
	private $dayOfWeek;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */	
	private $startDate;
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */	
	private $endDate;
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */	
	private $lastRun;	

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }	
	
	
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }	
	
    public function getMinute()
    {
        return $this->minute;
    }

    public function setMinute($minute)
    {
        $this->minute = $minute;
    }	
	
    public function getHour()
    {
        return $this->hour;
    }

    public function setHour($hour)
    {
        $this->hour = $hour;
    }
	
    public function getDayOfMonth()
    {
        return $this->dayOfMonth;
    }

    public function setDayOfMonth($dayOfMonth)
    {
        $this->dayOfMonth = $dayOfMonth;
	}
	
    public function getMonth()
    {
        return $this->month;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }	
	
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;
    }	
	
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
	}
	
    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }
	
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }
	
    public function getLastRun()
    {
        return $this->lastRun;
    }

    public function setLastRun(\DateTime $lastRun = null)
    {
        $this->lastRun = $lastRun;
    }	

}
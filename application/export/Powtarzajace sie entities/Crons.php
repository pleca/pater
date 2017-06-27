<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Crons
 *
 * @ORM\Table(name="crons")
 * @ORM\Entity
 */
class Crons
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="minute", type="string", length=2, nullable=false)
     */
    private $minute;

    /**
     * @var string
     *
     * @ORM\Column(name="hour", type="string", length=2, nullable=false)
     */
    private $hour;

    /**
     * @var string
     *
     * @ORM\Column(name="dayOfMonth", type="string", length=2, nullable=false)
     */
    private $dayofmonth;

    /**
     * @var string
     *
     * @ORM\Column(name="month", type="string", length=2, nullable=false)
     */
    private $month;

    /**
     * @var string
     *
     * @ORM\Column(name="dayOfWeek", type="string", length=2, nullable=false)
     */
    private $dayofweek;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $enddate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastRun", type="datetime", nullable=true)
     */
    private $lastrun;


}


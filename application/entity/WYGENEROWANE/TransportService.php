<?php
//ALTER TABLE transport_service CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE courier_id courier_id INT NOT NULL, CHANGE status_id status_id TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportService
 *
 * @ORM\Table(name="transport_service")
 * @ORM\Entity
 */
class TransportService
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
     * @ORM\Column(name="courier_id", type="integer", nullable=false)
     */
    private $courierId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_online", type="string", length=100, nullable=false)
     */
    private $nameOnline;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_id", type="boolean", nullable=false)
     */
    private $statusId;

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
    public function getCourierId()
    {
        return $this->courierId;
    }

    /**
     * @param int $courierId
     */
    public function setCourierId($courierId)
    {
        $this->courierId = $courierId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameOnline()
    {
        return $this->nameOnline;
    }

    /**
     * @param string $nameOnline
     */
    public function setNameOnline($nameOnline)
    {
        $this->nameOnline = $nameOnline;
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


}


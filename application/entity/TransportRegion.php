<?php
//ALTER TABLE transport_region CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE courier_id courier_id INT NOT NULL, CHANGE type_id type_id INT NOT NULL, CHANGE status_id status_id TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportRegion
 *
 * @ORM\Table(name="transport_region")
 * @ORM\Entity
 */
class TransportRegion
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
     * @var integer
     *
     * @ORM\Column(name="type_id", type="integer", nullable=false)
     */
    private $typeId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=false)
     */
    private $code;

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
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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


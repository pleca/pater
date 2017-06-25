<?php
//ALTER TABLE transport_service_option CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE region_id region_id INT NOT NULL, CHANGE service_id service_id INT NOT NULL, CHANGE tax_id tax_id INT NOT NULL, CHANGE status_id status_id TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportServiceOption
 *
 * @ORM\Table(name="transport_service_option")
 * @ORM\Entity
 */
class TransportServiceOption
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
     * @ORM\Column(name="region_id", type="integer", nullable=false)
     */
    private $regionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="service_id", type="integer", nullable=false)
     */
    private $serviceId;

    /**
     * @var string
     *
     * @ORM\Column(name="weight_from", type="decimal", precision=7, scale=0, nullable=true)
     */
    private $weightFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="weight_to", type="decimal", precision=7, scale=0, nullable=true)
     */
    private $weightTo;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=4, nullable=false)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_id", type="integer", nullable=false)
     */
    private $taxId;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_time", type="string", length=100, nullable=false)
     */
    private $deliveryTime;

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
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param int $regionId
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;
    }

    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @return string
     */
    public function getWeightFrom()
    {
        return $this->weightFrom;
    }

    /**
     * @param string $weightFrom
     */
    public function setWeightFrom($weightFrom)
    {
        $this->weightFrom = $weightFrom;
    }

    /**
     * @return string
     */
    public function getWeightTo()
    {
        return $this->weightTo;
    }

    /**
     * @param string $weightTo
     */
    public function setWeightTo($weightTo)
    {
        $this->weightTo = $weightTo;
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
     * @return int
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * @param int $taxId
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;
    }

    /**
     * @return string
     */
    public function getDeliveryTime()
    {
        return $this->deliveryTime;
    }

    /**
     * @param string $deliveryTime
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = $deliveryTime;
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


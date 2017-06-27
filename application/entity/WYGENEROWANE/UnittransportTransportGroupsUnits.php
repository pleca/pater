<?php
//ALTER TABLE unitTransport_transport_groups_units CHANGE transport_group_id transport_group_id INT AUTO_INCREMENT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnittransportTransportGroupsUnits
 *
 * @ORM\Table(name="unitTransport_transport_groups_units")
 * @ORM\Entity
 */
class UnittransportTransportGroupsUnits
{
    /**
     * @var integer
     *
     * @ORM\Column(name="transport_group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $transportGroupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="unit_id", type="integer", nullable=false)
     */
    private $unitId;

    /**
     * @return int
     */
    public function getTransportGroupId()
    {
        return $this->transportGroupId;
    }

    /**
     * @param int $transportGroupId
     */
    public function setTransportGroupId($transportGroupId)
    {
        $this->transportGroupId = $transportGroupId;
    }

    /**
     * @return int
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * @param int $unitId
     */
    public function setUnitId($unitId)
    {
        $this->unitId = $unitId;
    }


}


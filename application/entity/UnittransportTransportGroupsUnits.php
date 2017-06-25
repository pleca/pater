<?php



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


}


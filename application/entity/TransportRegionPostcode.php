<?php
//ALTER TABLE transport_region_postcode CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE region_id region_id INT NOT NULL, CHANGE status_id status_id TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransportRegionPostcode
 *
 * @ORM\Table(name="transport_region_postcode")
 * @ORM\Entity
 */
class TransportRegionPostcode
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
     * @var string
     *
     * @ORM\Column(name="post1", type="string", length=10, nullable=false)
     */
    private $post1;

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
     * @return string
     */
    public function getPost1()
    {
        return $this->post1;
    }

    /**
     * @param string $post1
     */
    public function setPost1($post1)
    {
        $this->post1 = $post1;
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


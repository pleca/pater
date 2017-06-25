<?php
//ALTER TABLE frazy_promocyjne_uzycia CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_frazy id_frazy INT NOT NULL, CHANGE id_zam id_zam INT NOT NULL, CHANGE id_user id_user INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FrazyPromocyjneUzycia
 *
 * @ORM\Table(name="frazy_promocyjne_uzycia")
 * @ORM\Entity
 */
class FrazyPromocyjneUzycia
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
     * @ORM\Column(name="id_frazy", type="integer", nullable=false)
     */
    private $idFrazy;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_zam", type="integer", nullable=false)
     */
    private $idZam;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="date", nullable=false)
     */
    private $data;

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
    public function getIdFrazy()
    {
        return $this->idFrazy;
    }

    /**
     * @param int $idFrazy
     */
    public function setIdFrazy($idFrazy)
    {
        $this->idFrazy = $idFrazy;
    }

    /**
     * @return int
     */
    public function getIdZam()
    {
        return $this->idZam;
    }

    /**
     * @param int $idZam
     */
    public function setIdZam($idZam)
    {
        $this->idZam = $idZam;
    }

    /**
     * @return int
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * @param int $idUser
     */
    public function setIdUser($idUser)
    {
        $this->idUser = $idUser;
    }

    /**
     * @return \DateTime
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \DateTime $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


}


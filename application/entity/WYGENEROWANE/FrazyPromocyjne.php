<?php
//ALTER TABLE frazy_promocyjne CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE max_uzyc max_uzyc INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FrazyPromocyjne
 *
 * @ORM\Table(name="frazy_promocyjne", uniqueConstraints={@ORM\UniqueConstraint(name="fraza", columns={"fraza"})})
 * @ORM\Entity
 */
class FrazyPromocyjne
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
     * @ORM\Column(name="fraza", type="string", length=128, nullable=false)
     */
    private $fraza;

    /**
     * @var integer
     *
     * @ORM\Column(name="wartosc", type="smallint", nullable=false)
     */
    private $wartosc;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_od", type="date", nullable=false)
     */
    private $dataOd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_do", type="date", nullable=false)
     */
    private $dataDo;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_uzyc", type="integer", nullable=false)
     */
    private $maxUzyc;

    /**
     * @var boolean
     *
     * @ORM\Column(name="klient_uzyc", type="boolean", nullable=false)
     */
    private $klientUzyc;

    /**
     * @var integer
     *
     * @ORM\Column(name="uzyto", type="integer", nullable=false)
     */
    private $uzyto;

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
     * @return string
     */
    public function getFraza()
    {
        return $this->fraza;
    }

    /**
     * @param string $fraza
     */
    public function setFraza($fraza)
    {
        $this->fraza = $fraza;
    }

    /**
     * @return int
     */
    public function getWartosc()
    {
        return $this->wartosc;
    }

    /**
     * @param int $wartosc
     */
    public function setWartosc($wartosc)
    {
        $this->wartosc = $wartosc;
    }

    /**
     * @return \DateTime
     */
    public function getDataOd()
    {
        return $this->dataOd;
    }

    /**
     * @param \DateTime $dataOd
     */
    public function setDataOd($dataOd)
    {
        $this->dataOd = $dataOd;
    }

    /**
     * @return \DateTime
     */
    public function getDataDo()
    {
        return $this->dataDo;
    }

    /**
     * @param \DateTime $dataDo
     */
    public function setDataDo($dataDo)
    {
        $this->dataDo = $dataDo;
    }

    /**
     * @return int
     */
    public function getMaxUzyc()
    {
        return $this->maxUzyc;
    }

    /**
     * @param int $maxUzyc
     */
    public function setMaxUzyc($maxUzyc)
    {
        $this->maxUzyc = $maxUzyc;
    }

    /**
     * @return bool
     */
    public function isKlientUzyc()
    {
        return $this->klientUzyc;
    }

    /**
     * @param bool $klientUzyc
     */
    public function setKlientUzyc($klientUzyc)
    {
        $this->klientUzyc = $klientUzyc;
    }

    /**
     * @return int
     */
    public function getUzyto()
    {
        return $this->uzyto;
    }

    /**
     * @param int $uzyto
     */
    public function setUzyto($uzyto)
    {
        $this->uzyto = $uzyto;
    }


}


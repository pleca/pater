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


}


<?php



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


}


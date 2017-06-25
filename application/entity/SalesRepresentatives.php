<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalesRepresentatives
 *
 * @ORM\Table(name="sales_representatives")
 * @ORM\Entity
 */
class SalesRepresentatives
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
     * @ORM\Column(name="first_name", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;


}


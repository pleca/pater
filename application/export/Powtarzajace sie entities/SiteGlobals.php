<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiteGlobals
 *
 * @ORM\Table(name="site_globals")
 * @ORM\Entity
 */
class SiteGlobals
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;


}


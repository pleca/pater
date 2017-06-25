<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AllegroTemplates
 *
 * @ORM\Table(name="allegro_templates")
 * @ORM\Entity
 */
class AllegroTemplates
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
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;


}


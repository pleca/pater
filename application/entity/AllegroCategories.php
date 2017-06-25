<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AllegroCategories
 *
 * @ORM\Table(name="allegro_categories")
 * @ORM\Entity
 */
class AllegroCategories
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

    /**
     * @var string
     *
     * @ORM\Column(name="category_id", type="string", length=20, nullable=false)
     */
    private $categoryId;


}


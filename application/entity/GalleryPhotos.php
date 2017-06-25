<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryPhotos
 *
 * @ORM\Table(name="gallery_photos", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity
 */
class GalleryPhotos
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
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=100, nullable=false)
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="desc", type="text", length=65535, nullable=false)
     */
    private $desc;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="text", length=255, nullable=false)
     */
    private $alt;

    /**
     * @var integer
     *
     * @ORM\Column(name="order", type="integer", nullable=false)
     */
    private $order;


}


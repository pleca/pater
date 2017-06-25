<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colors
 *
 * @ORM\Table(name="colors", indexes={@ORM\Index(name="IDX_C2BEC39F5DA0FB8", columns={"template_id"})})
 * @ORM\Entity
 */
class Colors
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
     * @ORM\Column(name="value", type="string", length=32, nullable=false)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDefault", type="boolean", nullable=false)
     */
    private $isdefault;

    /**
     * @var \Templates
     *
     * @ORM\ManyToOne(targetEntity="Templates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     * })
     */
    private $template;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isIsdefault()
    {
        return $this->isdefault;
    }

    /**
     * @param bool $isdefault
     */
    public function setIsdefault($isdefault)
    {
        $this->isdefault = $isdefault;
    }

    /**
     * @return Templates
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Templates $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }



}


<?php
//ALTER TABLE news_desc CHANGE id2 id2 INT AUTO_INCREMENT NOT NULL, CHANGE parent_id parent_id INT NOT NULL, CHANGE lang_id lang_id INT NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsDesc
 *
 * @ORM\Table(name="news_desc")
 * @ORM\Entity
 */
class NewsDesc
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id2", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id2;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="lang_id", type="integer", nullable=false)
     */
    private $langId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_url", type="text", length=255, nullable=false)
     */
    private $nameUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="desc", type="text", length=65535, nullable=false)
     */
    private $desc;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_short", type="text", length=255, nullable=false)
     */
    private $descShort;

    /**
     * @var string
     *
     * @ORM\Column(name="tag1", type="text", length=255, nullable=false)
     */
    private $tag1;

    /**
     * @var string
     *
     * @ORM\Column(name="tag2", type="text", length=255, nullable=false)
     */
    private $tag2;

    /**
     * @var string
     *
     * @ORM\Column(name="tag3", type="text", length=255, nullable=false)
     */
    private $tag3;

    /**
     * @return int
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * @param int $id2
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getLangId()
    {
        return $this->langId;
    }

    /**
     * @param int $langId
     */
    public function setLangId($langId)
    {
        $this->langId = $langId;
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
    public function getNameUrl()
    {
        return $this->nameUrl;
    }

    /**
     * @param string $nameUrl
     */
    public function setNameUrl($nameUrl)
    {
        $this->nameUrl = $nameUrl;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    /**
     * @return string
     */
    public function getDescShort()
    {
        return $this->descShort;
    }

    /**
     * @param string $descShort
     */
    public function setDescShort($descShort)
    {
        $this->descShort = $descShort;
    }

    /**
     * @return string
     */
    public function getTag1()
    {
        return $this->tag1;
    }

    /**
     * @param string $tag1
     */
    public function setTag1($tag1)
    {
        $this->tag1 = $tag1;
    }

    /**
     * @return string
     */
    public function getTag2()
    {
        return $this->tag2;
    }

    /**
     * @param string $tag2
     */
    public function setTag2($tag2)
    {
        $this->tag2 = $tag2;
    }

    /**
     * @return string
     */
    public function getTag3()
    {
        return $this->tag3;
    }

    /**
     * @param string $tag3
     */
    public function setTag3($tag3)
    {
        $this->tag3 = $tag3;
    }


}


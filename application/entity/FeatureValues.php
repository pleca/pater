<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeatureValues
 *
 * @ORM\Table(name="feature_values")
 * @ORM\Entity
 */
class FeatureValues
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
     * @ORM\Column(name="feature_id", type="integer", nullable=false)
     */
    private $featureId;


}


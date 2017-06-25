<?php
//ALTER TABLE newsletter_users CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE lang_id lang_id TINYINT(1) NOT NULL;

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsletterUsers
 *
 * @ORM\Table(name="newsletter_users")
 * @ORM\Entity
 */
class NewsletterUsers
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

    /**
     * @var boolean
     *
     * @ORM\Column(name="lang_id", type="boolean", nullable=false)
     */
    private $langId = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;


}


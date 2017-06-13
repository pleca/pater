<?php
namespace Application\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

require_once(CLASS_DIR . '/ValidatorTrait.php');
use Application\Classes\ValidatorTrait;
use Cms;

 
/**
 * @ORM\Entity 
 *
 * @ORM\Table(name="product_reviews")
 */
class ProductReview
{
	use ValidatorTrait;
	
	/** 
	 * @ORM\Id 
	 * @ORM\Column(type="integer", options={"unsigned"=true}) 
	 * @ORM\GeneratedValue 
	 */
    private $id;
    
	/** 
	 * @ORM\Column(type="integer", options={"unsigned"=true}) 
	 */
    private $productId;
	
	/** 
	 * @ORM\Column(type="integer", options={"unsigned"=true}) 
	 */
    private $customerId;
    
    /**
     * @ORM\Column(type="string", length=100)
     */	
	private $author;	

    /**
     *
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      minMessage = "Rating value must be at least {{ limit }}",
     *      maxMessage = "Rating value cannot be bigger than {{ limit }}"
     * )
     * @ORM\Column(type="smallint", length=1, options={"unsigned"=true})
     */		
	private $reviewValue;
	
    /**
     * @Assert\NotBlank(message = "product_review.comment_title.not_blank")
     * @ORM\Column(type="string", length=100)
     */
    private $commentTitle;
    
    /**
     * @Assert\NotBlank(message = "product_review.comment.not_blank")
     * @ORM\Column(type="text")
     */
    private $comment;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */	
	private $datePublished;
    
	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	private $active;  
	
    public function __construct()
    {
        $this->datePublished = new \DateTime();
        $this->active = 0;
    }
    
    public function getId()
    {
        return $this->id;
    }
	
    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
    
    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }
    
    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
    
    public function getReviewValue()
    {
        return $this->reviewValue;
    }

    public function setReviewValue($reviewValue)
    {
        $this->reviewValue = $reviewValue;
    }
    
    public function getCommentTitle()
    {
        return $this->commentTitle;
    }

    public function setCommentTitle($commentTitle)
    {
        $this->commentTitle = $commentTitle;
    }
    
    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getDatePublished()
    {
        return $this->datePublished;
    }

    public function setDatePublished(\DateTime $datePublished = null)
    {
        $this->datePublished = $datePublished;
    }   
    
    public function getActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }
    
	public function getPages($limit = 25) {
		$q = "SELECT COUNT(`id`) FROM `product_reviews` ";
		$v = Cms::$db->max($q);
		if ($v[0] < 1)
			$v[0] = 1;
		return ceil($v[0] / $limit);
	}    

}
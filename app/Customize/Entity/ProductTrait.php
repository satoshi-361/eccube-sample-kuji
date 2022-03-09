<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Product;

/**
  * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
	/**
     * @var string
     *
     * @ORM\Column(name="premium", type="string", length=255, nullable=true)
     */
    public $premium;
	
	/**
     * @var string
     *
     * @ORM\Column(name="niconico", type="string", length=255, nullable=true)
     */
    public $niconico;
	
	/**
     * @var string
     *
     * @ORM\Column(name="specifics", type="string", length=255, nullable=true)
     */
    public $specifics;
	
	/**
     * @var string
     *
     * @ORM\Column(name="limit_count", type="string", length=255, nullable=true)
     */
    public $limit_count;
	
	/**
     * @var int
     *
     * @ORM\Column(name="product_assist_id", type="integer")
     */
    public $product_assist_id;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    public $position;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sales_start", type="datetimetz", nullable=true)
     */
    public $sales_start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sales_end", type="datetimetz", nullable=true)
     */
    public $sales_end;

	/**
     * @var string
     *
     * @ORM\Column(name="animate_image", type="string", length=255, nullable=true)
     */
    private $animate_image;    

    /**
     * @var string
     *
     * @ORM\Column(name="twitter_tags", type="string", length=255, nullable=true)
     */
    private $twitter_tags;    

    /**
     * @var int
     * 
     * @ORM\Column(name="ship_count", type="integer", nullable=true)
     */

    private $ship_count;

    public function __construct()
    {
        $this->sales_start = new \DateTime('now');
        $this->sales_end = new \DateTime('now');
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this;
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSalesStart()
    {
        return $this->sales_start;
    }

    /**
     * @param \DateTime $sales_start
     *
     * @return $this;
     */
    public function setSalesStart($sales_start)
    {
        $this->sales_start = $sales_start;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSalesEnd()
    {
        return $this->sales_end;
    }

    /**
     * @param \DateTime $sales_end
     *
     * @return $this;
     */
    public function setSalesEnd($sales_end)
    {
        $this->sales_end = $sales_end;

        return $this;
    }

    public function getAnimateImage()
    {
        return $this->animate_image;
    }

    public function setAnimateImage($animate_image)
    {
        $this->animate_image = $animate_image;

        return $this;
    }

    public function getTwitterTags()
    {
        return $this->twitter_tags;
    }

    public function setTwitterTags($twitter_tags)
    {
        $this->twitter_tags = $twitter_tags;
        return $this;
    }

    /**
     * @return int
     */
    public function getShipCount()
    {
        return $this->ship_count;
    }

    /**
     * @param int $sc
     *
     * @return $this;
     */
    public function setShipCount($sc)
    {
        $this->ship_count = $sc;

        return $this;
    }

}
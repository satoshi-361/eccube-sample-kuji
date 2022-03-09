<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public $premium;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $channel;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $ticket;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $customer_rank;

    /**
     * @ORM\Column(type="integer")
     */
    private $wrong_count = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $old_mem_id;

    /**
     * Get wrong_count.
     *
     * @return int
     */
    public function getWrongCount()
    {
        return $wrong_count->id;
    }

    /**
     * Set wrong_count
     * 
     * @return this
     */
    public function setWrongCount($wrong_count)
    {
        $this->wrong_count = $wrong_count;

        return $this;
    }

    /**
     * Get old_mem_id.
     *
     * @return old_mem_id
     */
    public function getOldMemId()
    {
        return $this->old_mem_id;
    }

    /**
     * Set old_mem_id.
     *
     * @param string $old_mem_id
     *
     * @return this
     */
    public function setOldMemId($old_mem_id)
    {
        $this->old_mem_id = $old_mem_id;
        
        return $this;
    }
    
}
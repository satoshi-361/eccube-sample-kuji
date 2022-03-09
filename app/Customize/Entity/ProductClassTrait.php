<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{
	/**
     * @var int
     *
     * @ORM\Column(name="remain_status", type="integer", nullable = true)
     */
    protected $remainStatus;

    /**
     * @return int
     */
    public function getRemainStatus()
    {
        return $this->remainStatus;
    }

    /**
     * @param int $remainStatus
     *
     * @return $this;
     */
    public function setRemainStatus($remainStatus)
    {
        $this->remainStatus = $remainStatus;

        return $this;
    }
}
<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\CartItem")
 */
trait CartItemTrait
{
    /**
     * @var int
     * 
     * @ORM\Column(name="ship_count", type="integer", nullable=true)
     */

    private $ship_count;

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

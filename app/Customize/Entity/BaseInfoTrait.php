<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\BaseInfo")
 */
trait BaseInfoTrait
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="option_nico_point", type="boolean", options={"default":true})
     */
    private $option_nico_point = true;
    
    /**
     * Set optionNicoPoint
     *
     * @param boolean $option_nico_point
     *
     * @return BaseInfo
     */
    public function setOptionNicoPoint($option_nico_point)
    {
        $this->option_nico_point = $option_nico_point;
        return $this;
    }

    /**
     * Get optionNicoPoint
     *
     * @return boolean
     */
    public function isOptionNicoPoint()
    {
        return $this->option_nico_point;
    }
}
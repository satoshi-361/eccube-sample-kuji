<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
  * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $lotteryCount;

    /**
     * @return int
     */
    public function getLotteryCount()
    {
        return $this->lotteryCount;
    }

    /**
     * @param int $lotteryCount
     *
     * @return $this;
     */
    public function setLotteryCount($lotteryCount)
    {
        $this->lotteryCount = $lotteryCount;

        return $this;
    }

    /**
     * @ORM\OneToMany(
     *      targetEntity="\Plugin\PrizeShow\Entity\Config",
     *      mappedBy="orderId",
     *      cascade={"persist", "remove"}
     * )
     */
    public $prizes;

    public function __construct()
    {
        $this->prizes = new ArrayCollection();
    }

    public function getPrizes() : Collection
    {
        return $this->prizes;
    }

    public function addPrize($prize)
    {
        $this->prizes->add($prize);
    }

        /**
     * @var string
     *
     * @ORM\Column(name="add_nico_point", type="decimal", precision=12, scale=0, options={"unsigned":true,"default":0})
     */
    private $add_nico_point = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="use_nico_point", type="decimal", precision=12, scale=0, options={"unsigned":true,"default":0})
     */
    private $use_nico_point = '0';

    /**
     * Set addNicoPoint
     *
     * @param string $addNicoPoint
     *
     * @return Order
     */
    public function setAddNicoPoint($addNicoPoint)
    {
        $this->add_nico_point = $addNicoPoint;

        return $this;
    }

    /**
     * Get addNicoPoint
     *
     * @return string
     */
    public function getAddNicoPoint()
    {
        return $this->add_nico_point;
    }

    /**
     * Set useNicoPoint
     *
     * @param string $useNicoPoint
     *
     * @return Order
     */
    public function setUseNicoPoint($useNicoPoint)
    {
        $this->use_nico_point = $useNicoPoint;

        return $this;
    }

    /**
     * Get useNicoPoint
     *
     * @return string
     */
    public function getUseNicoPoint()
    {
        return $this->use_nico_point;
    }
}
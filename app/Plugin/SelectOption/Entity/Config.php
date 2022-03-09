<?php

namespace Plugin\SelectOption\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_select_option_config")
 * @ORM\Entity(repositoryClass="Plugin\SelectOption\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="class_id", type="integer")
     */
    private $class_id;

    /**
     * @var int
     *
     * @ORM\Column(name="real_id", type="integer")
     */
    private $real_id;

    /**
     * @var string
     *
     * @ORM\Column(name="real_name", type="string")
     */
    private $real_name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getClassId()
    {
        return $this->class_id;
    }

    /**
     * @param int $class_id
     *
     * @return $this;
     */
    public function setClassId($class_id)
    {
        $this->class_id = $class_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getRealId()
    {
        return $this->real_name;
    }

    /**
     * @param string $int
     *
     * @return $this;
     */
    public function setRealId($real_id)
    {
        $this->real_id = $real_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->real_name;
    }

    /**
     * @param string $name
     *
     * @return $this;
     */
    public function setRealName($name)
    {
        $this->real_name = $name;

        return $this;
    }


}

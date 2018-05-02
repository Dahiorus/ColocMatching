<?php

namespace ColocMatching\CoreBundle\Repository\Filter\Pageable;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Sort option for queries
 * @author Dahiorus
 */
class Sort
{
    /**
     * The property to sort by
     * @var string
     *
     * @Assert\NotBlank
     */
    private $property;

    /**
     * The sorting direction
     * @var string
     *
     * @Assert\Choice(choices={ Order::DESC, Order::ASC }, strict=true)
     */
    private $direction;


    /**
     * Creates a Sort
     *
     * @param string $property The sorting property
     * @param string $direction [optional] The sorting direction
     *
     * @return Sort
     */
    public static function create(string $property, string $direction = Order::ASC) : Sort
    {
        if (!in_array(strtolower($direction), array (Order::ASC, Order::DESC), true))
        {
            throw new \InvalidArgumentException("Parameter '$direction' is not a valid direction");
        }

        $sort = new self();

        $sort->property = trim($property);
        $sort->direction = strtolower($direction);

        return $sort;
    }


    public function __toString()
    {
        return "Sort[" . $this->property . ": " . $this->direction . "]";
    }


    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }


    public function setProperty(string $property)
    {
        $this->property = $property;

        return $this;
    }


    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }


    public function setDirection(string $direction)
    {
        $this->direction = $direction;

        return $this;
    }

}

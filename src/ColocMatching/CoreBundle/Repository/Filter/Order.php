<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

/**
 * Item for queries to sort by one property
 * @author Dahiorus
 */
class Order
{
    const ASC = "asc";
    const DESC = "desc";

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $direction;


    /**
     * Sort constructor.
     *
     * @param string $property The sorting property
     * @param string $direction [optional] The sorting direction
     */
    public function __construct(string $property, string $direction = self::ASC)
    {
        if (!in_array(strtolower($direction), array (self::ASC, self::DESC), true))
        {
            throw new \InvalidArgumentException("Parameter '$direction' is not a valid direction");
        }

        $this->property = $property;
        $this->direction = strtolower($direction);
    }


    public function __toString()
    {
        return "Order[" . $this->property . "=> " . $this->direction . "]";
    }


    /**
     * @return string
     */
    public function getProperty() : string
    {
        return $this->property;
    }


    /**
     * @return string
     */
    public function getDirection() : string
    {
        return $this->direction;
    }

}
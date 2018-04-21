<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

/**
 * Sort option for queries
 * @author Dahiorus
 */
class Sort
{
    /**
     * @var Order[]
     */
    private $orders;


    /**
     * Creates a new Sort with the key-value array of (property => direction)
     *
     * @param array $orders The key-value array representing the Orders
     *
     * @return Sort
     */
    public static function create(array $orders)
    {
        $sort = new self();

        foreach ($orders as $property => $direction)
        {
            $sort->orders[] = new Order($property, $direction);
        }

        return $sort;
    }


    /**
     * Creates a new ascending Sort with the array of properties
     *
     * @param array $properties The properties
     *
     * @return Sort
     */
    public static function ascending(array $properties)
    {
        $sort = new self();

        foreach ($properties as $property)
        {
            $sort->orders[] = new Order($property);
        }

        return $sort;
    }


    /**
     * Creates a new descending Sort with the array of properties
     *
     * @param array $properties The properties
     *
     * @return Sort
     */
    public static function descending(array $properties)
    {
        $sort = new self();

        foreach ($properties as $property)
        {
            $sort->orders[] = new Order($property, Order::DESC);
        }

        return $sort;
    }


    public function __toString()
    {
        return "Sort[orders = {" . implode(", ", $this->orders) . "}]";
    }


    /**
     * Gets the Sort as key-value array with the order property as key and the order direction as value
     * @return array
     */
    public function getSort() : array
    {
        $sort = array ();

        foreach ($this->orders as $order)
        {
            $sort[ $order->getProperty() ] = $order->getDirection();
        }

        return $sort;
    }

}

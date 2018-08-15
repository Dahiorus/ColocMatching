<?php

namespace App\Core\Repository\Filter\Pageable;

use Symfony\Component\Validator\Constraints as Assert;

class PageRequest implements Pageable
{
    /**
     * Paging start (from 1)
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(1)
     */
    private $page;

    /**
     * Paging size
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $size;

    /**
     * Sorting properties
     * @var Sort[]
     *
     * @Assert\Valid
     */
    private $sorts = array ();


    /**
     * Creates a PageRequest from the array parameters. The array must have indexes:
     *   - page: int
     *   - size: int
     *   - sorts: key-value map
     * If the parameters array is empty, returns null.
     *
     * @param array $parameters The page request parameters
     *
     * @return PageRequest|null
     */
    public static function create(array $parameters)
    {
        if (empty($parameters))
        {
            return null;
        }

        return new self($parameters["page"], $parameters["size"], $parameters["sorts"]);
    }


    /**
     * PageRequest constructor.
     *
     * @param int $page [optional] The page number (from 1)
     * @param int $size [optional] The page size
     * @param array $sorts The page sorting attributes
     */
    public function __construct(int $page = 1, int $size = 20, array $sorts = array ())
    {
        $this->page = $page;
        $this->size = $size;

        foreach ($sorts as $property => $direction)
        {
            $this->sorts[] = Sort::create($property, $direction);
        }
    }


    public function __toString()
    {
        return "PageRequest[page = " . $this->page . ", size = " . $this->size
            . ", sorts = " . json_encode($this->getSortingArray()) . "]";
    }


    public function getPage()
    {
        return $this->page;
    }


    public function setPage(?int $page)
    {
        $this->page = $page;

        return $this;
    }


    public function getSize()
    {
        return $this->size;
    }


    public function setSize(?int $size)
    {
        $this->size = $size;

        return $this;
    }


    public function getOffset() : int
    {
        return ($this->page - 1) * $this->size;
    }


    /**
     * Paging sort
     *
     * @return array<string, string>
     */
    public function getSorts() : array
    {
        return $this->sorts;
    }


    /**
     * @param Sort[] $sorts
     *
     * @return $this
     */
    public function setSorts(array $sorts = array ())
    {
        $this->sorts = $sorts;

        return $this;
    }


    public function addSort(Sort $sort = null)
    {
        if (!empty($sort) && !in_array($sort, $this->sorts, true))
        {
            $this->sorts[] = $sort;
        }

        return $this;
    }


    /**
     * Gets an associative array from the list of Sort
     *
     * @return array
     */
    private function getSortingArray()
    {
        $values = array ();

        foreach ($this->sorts as $sort)
        {
            $values[ $sort->getProperty() ] = $sort->getDirection();
        }

        return $values;
    }

}

<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

class PageRequest implements Pageable
{
    /**
     * Paging start (from 1)
     *
     * @var integer
     */
    private $page;

    /**
     * Paging size
     *
     * @var integer
     */
    private $size;

    /**
     * @var Sort
     */
    private $sort;


    /**
     * Creates a PageRequest from the array parameters. The array must have indexes:
     *   - page: int
     *   - size: int
     *   - sort: key-value map
     *
     * @param array $parameters The page request parameters
     *
     * @return PageRequest
     */
    public static function create(array $parameters) : PageRequest
    {
        return new self($parameters["page"], $parameters["size"], $parameters["sort"]);
    }


    /**
     * PageRequest constructor.
     *
     * @param int $page [optional] The page number (from 1)
     * @param int $size [optional] The page size
     * @param array $sort The page sorting attributes
     */
    public function __construct(int $page = 1, int $size = 20, array $sort = array ())
    {
        $this->page = $page;
        $this->size = $size;
        $this->sort = Sort::create($sort);
    }


    public function __toString()
    {
        return "PageRequest[page = " . $this->page . ", size = " . $this->size . ", sort = " . $this->sort . "]";
    }


    public function getPage() : int
    {
        return $this->page;
    }


    public function getSize() : int
    {
        return $this->size;
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
    public function getSort() : array
    {
        return $this->sort->getSort();
    }

}

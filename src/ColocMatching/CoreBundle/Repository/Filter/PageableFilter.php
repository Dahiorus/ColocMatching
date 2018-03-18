<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use Swagger\Annotations as SWG;

/**
 * Filter for paginated listing
 *
 * @author Dahiorus
 */
class PageableFilter
{
    const ORDER_ASC = "asc";

    const ORDER_DESC = "desc";

    /**
     * Pagination start (from 1)
     *
     * @var integer
     *
     * @SWG\Property(description="Page number", default=1)
     */
    protected $page = 1;

    /**
     * Pagination size
     *
     * @var integer
     *
     * @SWG\Property(description="Page size", default=20)
     */
    protected $size = 20;

    /**
     * Order direction (ASC or DESC)
     *
     * @var string
     *
     * @SWG\Property(description="Sorting order", enum={ "asc", "desc" }, default="asc")
     */
    protected $order = self::ORDER_ASC;

    /**
     * Attribute to sort by
     *
     * @var string
     *
     * @SWG\Property(description="Attribute name to sort by", default="createdAt")
     */
    protected $sort = "createdAt";


    public function __toString() : string
    {
        return get_class($this) . "[page = " . $this->page . ", size = " . $this->size . ", order = " . $this->order
            . ", sort = " . $this->sort . "]";
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


    public function getOrder()
    {
        return $this->order;
    }


    public function setOrder(?string $order)
    {
        $this->order = strtolower($order);

        return $this;
    }


    public function getSort()
    {
        return $this->sort;
    }


    public function setSort(?string $sort)
    {
        $this->sort = $sort;

        return $this;
    }


    public function getOffset() : int
    {
        return ($this->page - 1) * $this->size;
    }

}
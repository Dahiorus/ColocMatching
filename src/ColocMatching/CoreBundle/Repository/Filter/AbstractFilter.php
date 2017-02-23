<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractFilter {

    const ORDER_ASC = "ASC";

    const ORDER_DESC = "DESC";

    /**
     * Pagination start (from 1)
     *
     * @var int
     * @Assert\GreaterThanOrEqual(value=1)
     */
    protected $page = RequestConstants::DEFAULT_PAGE;

    /**
     * Paginaion size
     *
     * @var int
     * @Assert\GreaterThanOrEqual(value=1)
     */
    protected $size = RequestConstants::DEFAULT_LIMIT;

    /**
     * Order direction (ASC or DESC)
     *
     * @var string
     * @Assert\Choice(choices={ AbstractFilter::ORDER_ASC, AbstractFilter::ORDER_DESC }, strict=true)
     */
    protected $order = RequestConstants::DEFAULT_ORDER;

    /**
     * Attribute to sort by
     *
     * @var string
     */
    protected $sort = RequestConstants::DEFAULT_SORT;


    public function __toString(): string {
        return sprintf("AbstractFilter [page: %d, size: %d, order: '%s', sort: '%s']", $this->page, $this->size, 
            $this->order, $this->sort);
    }


    public function getPage(): int {
        return $this->page;
    }


    public function setPage($page) {
        $this->page = $page;
        return $this;
    }


    public function getSize(): int {
        return $this->size;
    }


    public function setSize($size) {
        $this->size = $size;
        return $this;
    }


    public function getOrder(): string {
        return $this->order;
    }


    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }


    public function getSort(): string {
        return $this->sort;
    }


    public function setSort($sort) {
        $this->sort = $sort;
        return $this;
    }


    public function getOffset(): int {
        return ($this->page - 1) * $this->size;
    }


    /**
     * Build a filtering criteria from the filter
     * @return Criteria
     */
    public abstract function buildCriteria(): Criteria;

}
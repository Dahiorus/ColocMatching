<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;

class PageableFilter {

    const ORDER_ASC = "ASC";

    const ORDER_DESC = "DESC";

    /**
     * Pagination start (from 1)
     *
     * @var int
     */
    protected $page = RequestConstants::DEFAULT_PAGE;

    /**
     * Pagination size
     *
     * @var int
     */
    protected $size = RequestConstants::DEFAULT_LIMIT;

    /**
     * Order direction (ASC or DESC)
     *
     * @var string
     */
    protected $order = RequestConstants::DEFAULT_ORDER;

    /**
     * Attribute to sort by
     *
     * @var string
     */
    protected $sort = RequestConstants::DEFAULT_SORT;


    public function __toString(): string {
        return sprintf("PageableFilter [page: %d, size: %d, order: '%s', sort: '%s']", $this->page, $this->size,
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

}
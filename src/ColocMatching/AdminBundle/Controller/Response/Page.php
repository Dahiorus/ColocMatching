<?php

namespace ColocMatching\AdminBundle\Controller\Response;

use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;

class Page {

    /**
     * @var array
     */
    private $content;

    /**
     * @var integer
     */
    private $page;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $numberElements;

    /**
     * @var integer
     */
    private $totalElements;

    /**
     * @var string
     */
    private $order;

    /**
     * @var string
     */
    private $sort;


    /**
     * Page constructor.
     *
     * @param PageableFilter $pageable Pagination information
     * @param array $content           The page content
     * @param int $total               Total count
     */
    public function __construct(PageableFilter $pageable, array $content, int $total) {
        $this->content = $content;
        $this->numberElements = count($content);
        $this->totalElements = $total;
        $this->page = $pageable->getPage();
        $this->size = $pageable->getSize();
        $this->order = $pageable->getOrder();
        $this->sort = $pageable->getSort();
    }


    public function __toString() {
        return "Page [page=" . $this->page . ", size=" . $this->size . ", numberElements=" . $this->numberElements
            . ", totalElements=" . $this->totalElements . ", order=" . $this->order . ", sort=" . $this->sort . "]";
    }


    public function getContent() {
        return $this->content;
    }


    public function setContent(array $content) {
        $this->content = $content;
    }


    public function getPage() {
        return $this->page;
    }


    public function setPage(int $page) {
        $this->page = $page;

        return $this;
    }


    public function getSize() {
        return $this->size;
    }


    public function setSize(int $size) {
        $this->size = $size;

        return $this;
    }


    public function getNumberElements() {
        return $this->numberElements;
    }


    public function setNumberElements(int $numberElements) {
        $this->numberElements = $numberElements;

        return $this;
    }


    public function getTotalElements() {
        return $this->totalElements;
    }


    public function setTotalElements(int $totalElements) {
        $this->totalElements = $totalElements;

        return $this;
    }


    public function getSort() {
        return $this->sort;
    }


    public function setSort($sort) {
        $this->sort = $sort;

        return $this;
    }


    public function getOrder() {
        return $this->order;
    }


    public function setOrder($order) {
        $this->order = $order;

        return $this;
    }


    /**
     * Total pages
     *
     * @return integer
     */
    public function getTotalPages() : int {
        if ($this->size == 0) {
            return 1;
        }

        return (int)ceil($this->totalElements / $this->size);
    }


    public function hasPrev() {
        return $this->page > 1;
    }


    public function hasNext() {
        return $this->page + 1 <= $this->getTotalPages();
    }


    public function isFirst() {
        return !$this->hasPrev();
    }


    public function isLast() {
        return !$this->hasNext();
    }

}
<?php

namespace ColocMatching\RestBundle\Controller\Response;

use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *   definition="PageResponse", description="Response container for entity collection with pagination",
 *   discriminator="content"
 * )
 *
 * @author Dahiorus
 */
class PageResponse extends AbstractResponse {

    /**
     * @var integer
     *
     * @SWG\Property(description="Page number")
     */
    private $page;

    /**
     * @var integer
     *
     * @SWG\Property(description="Page size")
     */
    private $size;

    /**
     * @var integer
     *
     * @JMS\SerializedName("numberElements")
     * @SWG\Property(description="Number of elements")
     */
    private $numberElements;

    /**
     * @var integer
     *
     * @JMS\SerializedName("totalElements")
     * @SWG\Property(description="Number of total elements")
     */
    private $totalElements;

    /**
     * @var string
     *
     * @SWG\Property(description="Order direction")
     */
    private $order;

    /**
     * @var string
     *
     * @SWG\Property(description="Sort attribute name")
     */
    private $sort;

    /**
     * @var string
     *
     * @SWG\Property(description="Next page URI")
     */
    private $next;

    /**
     * @var string
     *
     * @SWG\Property(description="Previous page URI")
     */
    private $prev;


    public function __construct(array $data, string $link) {
        parent::__construct($data, $link);

        $this->numberElements = count($data);
    }


    public function __toString() {

        return "PageResponse [page=" . $this->page . ", size=" . $this->size . ", numberElements=" . $this->numberElements
            . ", totalElements=" . $this->totalElements . ", order=" . $this->order . ", sort=" . $this->sort
            . ", hasPrev=" . $this->hasPrev() . ", hasNext=" . $this->hasNext() . ", isFirst=" . $this->isFirst()
            . ", isLast=" . $this->isLast() . "]";
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


    public function getNext() {
        return $this->next;
    }


    public function setNext($next) {
        $this->next = $next;

        return $this;
    }


    public function getPrev() {
        return $this->prev;
    }


    public function setPrev($prev) {
        $this->prev = $prev;

        return $this;
    }


    /**
     * Total pages
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("totalPages")
     * @JMS\Type("integer")
     * @SWG\Property(property="totalPages", type="integer", description="Total pages")
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


    /**
     * Set previous and next link for this PageResponse
     */
    public function setRelationLinks() {
        $self = $this->link;

        if ($this->hasPrev()) {
            $prev = preg_replace("/page=\d+/", 'page=' . ($this->page - 1), $self);
            $this->setPrev($prev);
        }

        if ($this->hasNext()) {
            $pageRegEx = "/page=\d+/";

            if (preg_match($pageRegEx, $self) > 0) {
                $next = preg_replace($pageRegEx, 'page=' . ($this->page + 1), $self);
            }
            else {
                $separator = (preg_match('/\?/', $self) > 0) ? '&' : '?';
                $next = $self . $separator . 'page=' . ($this->page + 1);
            }

            $this->setNext($next);
        }
    }

}
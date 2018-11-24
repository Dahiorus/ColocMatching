<?php

namespace App\Core\DTO;

class Collection implements \IteratorAggregate
{
    /**
     * Response content
     * @var array
     */
    protected $content;

    /**
     * Response content size
     * @var integer
     */
    protected $count;

    /**
     * Response total count
     * @var integer
     */
    protected $total;


    public function __construct(array $content, int $total)
    {
        $this->content = $content;
        $this->count = count($content);
        $this->total = $total;
    }


    public function __toString()
    {
        return get_class($this) . " [count=" . $this->count . ", total=" . $this->total . "]";
    }


    public function getContent() : array
    {
        return $this->content;
    }


    public function getCount() : int
    {
        return $this->count;
    }


    public function getTotal() : int
    {
        return $this->total;
    }


    public function getIterator()
    {
        return new \ArrayIterator($this->content);
    }

}
<?php

namespace App\Core\DTO;

use App\Core\Repository\Filter\Pageable\Pageable;
use App\Core\Repository\Filter\Pageable\Sort;

class Page extends Collection
{
    /**
     * Response page
     * @var integer
     */
    private $page;

    /**
     * Response size
     * @var integer
     */
    private $size = 0;

    /**
     * Response sorting filter
     * @var Sort[]
     */
    private $sorts = array ();


    public function __construct(Pageable $pageable, array $content, int $total)
    {
        parent::__construct($content, $total);

        $this->page = $pageable->getPage();
        $this->size = $pageable->getSize();
        $this->sorts = $pageable->getSorts();
    }


    public function __toString()
    {
        $sorts = empty($this->sorts) ? null : implode(", ", array_map(function (Sort $sort) {
            return $sort->__toString();
        }, $this->sorts));

        return parent::__toString() . "[page=" . $this->page . ", size=" . $this->size . ", sorts={" . $sorts . "}]";
    }


    public function getPage() : int
    {
        return $this->page;
    }


    public function getSize() : int
    {
        return $this->size;
    }


    public function getSorts() : array
    {
        return $this->sorts;
    }

}

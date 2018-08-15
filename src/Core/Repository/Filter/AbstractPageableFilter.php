<?php

namespace App\Core\Repository\Filter;

use App\Core\Repository\Filter\Pageable\Pageable;

class AbstractPageableFilter
{
    /**
     * @var Pageable
     */
    private $pageable;


    public function getPageable()
    {
        return $this->pageable;
    }


    public function setPageable(Pageable $pageable = null)
    {
        $this->pageable = $pageable;

        return $this;
    }

}

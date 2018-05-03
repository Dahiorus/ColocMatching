<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;

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

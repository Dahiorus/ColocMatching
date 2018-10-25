<?php

namespace App\Core\Repository\Filter;

use App\Core\Repository\Filter\Pageable\Pageable;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractPageableFilter
{
    /**
     * @var Pageable
     * @Assert\Valid
     * @Serializer\Type("App\Core\Repository\Filter\Pageable\PageRequest")
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

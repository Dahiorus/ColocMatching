<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\DTO\Visit\VisitableDto;

/**
 * Interface to implement the Visitor pattern
 *
 * @author Dahiorus
 */
interface VisitorInterface
{
    public function visit(VisitableDto $visitable) : void;
}
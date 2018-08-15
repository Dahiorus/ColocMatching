<?php

namespace App\Core\Service;

use App\Core\DTO\Visit\VisitableDto;

/**
 * Interface to implement the Visitor pattern
 *
 * @author Dahiorus
 */
interface VisitorInterface
{
    public function visit(VisitableDto $visitable) : void;
}
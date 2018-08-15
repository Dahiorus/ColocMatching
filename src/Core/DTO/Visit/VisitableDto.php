<?php

namespace App\Core\DTO\Visit;

use App\Core\DTO\DtoInterface;
use App\Core\Service\VisitorInterface;

/**
 * Interface to implement to accept a visitor
 *
 * @author Dahiorus
 */
interface VisitableDto extends DtoInterface
{
    public function accept(VisitorInterface $visitor);
}
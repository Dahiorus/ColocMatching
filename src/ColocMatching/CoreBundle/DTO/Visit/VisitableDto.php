<?php

namespace ColocMatching\CoreBundle\DTO\Visit;

use ColocMatching\CoreBundle\DTO\DtoInterface;
use ColocMatching\CoreBundle\Service\VisitorInterface;

/**
 * Interface to implement to accept a visitor
 *
 * @author Dahiorus
 */
interface VisitableDto extends DtoInterface
{
    public function accept(VisitorInterface $visitor);
}
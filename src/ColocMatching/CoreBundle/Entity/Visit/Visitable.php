<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Service\VisitorInterface;

/**
 * An entity which implements this interface creates a visit each time it
 * is loaded
 *
 * @author Dahiorus
 */
interface Visitable extends EntityInterface {

    public function accept(VisitorInterface $visitor);
}
<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\Entity\Visit\Visitable;

/**
 * Interface to implement the Visitor pattern
 *
 * @author Dahiorus
 */
interface VisitorInterface {

    public function visit(Visitable $visitable);
}
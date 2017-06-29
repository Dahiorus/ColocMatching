<?php

namespace ColocMatching\CoreBundle\Entity\Visit;

use Doctrine\Common\Collections\Collection;

/**
 * An entity which implements this interface creates a visit each time it
 * is loaded
 *
 * @author Dahiorus
 */
interface Visitable {


    /**
     * Gets the visits of the visitable
     *
     * @return Collection
     */
    public function getVisits();


    /**
     * Sets the visits of the visitable
     *
     * @param Collection $visits
     */
    public function setVisits(Collection $visits = null);


    /**
     * Adds a visit for the visitable
     *
     * @param Visit $visit
     */
    public function addVisit(Visit $visit = null);

}
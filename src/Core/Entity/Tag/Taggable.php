<?php

namespace App\Core\Entity\Tag;

use App\Core\Entity\EntityInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Interface to implement to add tags on an entity
 *
 * @author Dahiorus
 */
interface Taggable extends EntityInterface
{
    /**
     * Gets the tags
     * @return Collection<Tag>
     */
    public function getTags() : Collection;


    /**
     * Sets the tags
     *
     * @param Collection<Tag> $tags The tags
     *
     * @return $this
     */
    public function setTags(Collection $tags);

}

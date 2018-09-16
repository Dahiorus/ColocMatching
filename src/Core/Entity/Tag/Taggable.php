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
    public function setTags(Collection $tags = null);


    /**
     * Adds a tag
     *
     * @param Tag|null $tag The tag to add
     *
     * @return $this
     */
    public function addTag(Tag $tag = null);


    /**
     * Removes a tag
     *
     * @param Tag|null $tag The tag to remove
     */
    public function removeTag(Tag $tag = null) : void;


    /**
     * Indicates if this Taggable has the specified tag
     *
     * @param Tag|null $tag The tag
     *
     * @return bool
     */
    public function hasTag(Tag $tag = null) : bool;

}

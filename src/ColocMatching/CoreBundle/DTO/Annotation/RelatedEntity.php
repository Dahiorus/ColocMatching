<?php

namespace ColocMatching\CoreBundle\DTO\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Used to indicate the related entity class of a DTO attribute. In general, it is used on entityId attributes.
 *
 * @author Dahiorus
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class RelatedEntity
{
    /**
     * The target class FQCN
     * @var string
     *
     * @Required
     */
    public $targetClass;

    /**
     * The target property in the DTO entity class
     * @var string
     *
     * @Required
     */
    public $targetProperty;


    /**
     * @return string
     */
    public function getTargetClass() : string
    {
        return $this->targetClass;
    }


    /**
     * @return string
     */
    public function getTargetProperty() : string
    {
        return $this->targetProperty;
    }

}

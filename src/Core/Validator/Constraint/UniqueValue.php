<?php

namespace App\Core\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @author Dahiorus
 */
class UniqueValue extends Constraint
{
    /** @var string */
    public $message = "This value is already used";

    /**
     * The properties to validate
     * @var string[]|string
     */
    public $properties;

    /**
     * The property to map to error message
     * @var string
     */
    public $errorProperty = null;

    /**
     * If null value in properties should be ignored
     * @var bool
     */
    public $ignoreNull = true;


    public function getTargets()
    {
        return array (self::CLASS_CONSTRAINT);
    }


    public function getRequiredOptions()
    {
        return array ("properties");
    }


    public function getDefaultOption()
    {
        return "properties";
    }

}
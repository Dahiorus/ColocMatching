<?php

namespace App\Core\Exception;

use App\Core\Validator\ValidationError;
use Symfony\Component\Form\FormErrorIterator;

/**
 * Extension of Bad request error with form error management
 *
 * @author Dahiorus
 */
class InvalidFormException extends ColocMatchingException
{
    /**
     * @var ValidationError[]
     */
    protected $errors = array ();


    /**
     * InvalidFormException constructor.
     *
     * @param string $formClass The string representation of the invalid form
     * @param FormErrorIterator $formErrors The form errors list
     */
    public function __construct(string $formClass, FormErrorIterator $formErrors)
    {
        parent::__construct(
            "Invalid form data in '" . $formClass . "': " . $formErrors->count() . " errors found", 400);

        foreach ($formErrors as $formError)
        {
            $this->errors[] = new ValidationError($formError->getOrigin()->getName(), $formError->getMessage(),
                $formError->getCause());
        }
    }


    /**
     * @return ValidationError[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }


    public function getDetails() : array
    {
        $details = parent::getDetails();
        $details["errors"] = array_map(function (ValidationError $error) {
            return array (
                "property" => $error->getPropertyName(),
                "message" => $error->getMessage()
            );
        }, $this->errors);

        return $details;
    }

}

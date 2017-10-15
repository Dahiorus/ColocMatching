<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\Form\FormErrorIterator;

/**
 * Extension of Bad request error with form error management
 *
 * @author brondon.ung
 */
final class InvalidFormException extends ColocMatchingException {

    /**
     * @var FormErrorIterator
     */
    protected $formError;


    public function __construct(string $message, FormErrorIterator $formError) {
        parent::__construct($message, 422);

        $this->formError = $formError;
    }


    public function getFormError() : FormErrorIterator {
        return $this->formError;
    }


    public function getDetails() : array {
        $details = parent::getDetails();
        $errors = array ();

        foreach ($this->formError as $error) {
            $fieldName = $error->getOrigin()->getName();
            $errors[ $fieldName ] = $error->getMessage();
        }

        $details["errors"] = $errors;

        return $details;
    }

}

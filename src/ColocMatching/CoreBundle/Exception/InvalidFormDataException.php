<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Extension of Bad request error with form error management
 *
 * @author brondon.ung
 */
class InvalidFormDataException extends BadRequestHttpException {

    /** @var FormErrorIterator */
    protected $formError;


    public function __construct(string $message, FormErrorIterator $formError) {
        parent::__construct($message);

        $this->formError = $formError;
    }


    public function getFormError(): FormErrorIterator {
        return $this->formError;
    }


    public function toJSON(): string {
        $errors = array ();

        foreach ($this->formError as $error) {
            $inputName = $error->getOrigin()->getName();
            $errors[$inputName] = $error->getMessage();
        }

        return json_encode(array ('message' => $this->message, 'errors' => $errors));
    }

}

<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;

/**
 * Description of InvalidDataForm
 *
 * @author brondon.ung
 */
class InvalidFormDataException extends \RuntimeException {

    /** @var FormErrorIterator */
    protected $formError;


    public function __construct(string $message, FormErrorIterator $formError) {
        parent::__construct($message);
        
        $this->formError = $formError;
    }


    public function getFormError() {
        return $this->formError;
    }


    public function toJSON() {
        $errors = array ();
        
        foreach ($this->formError as /** @var FormError */
        $error) {
            $inputName = $error->getOrigin()->getName();
            $errors[$inputName] = $error->getMessage();
        }
        
        return json_encode(array ('message' => $this->message, 'errors' => $errors));
    }

}

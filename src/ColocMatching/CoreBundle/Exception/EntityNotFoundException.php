<?php

namespace ColocMatching\CoreBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityNotFoundException extends NotFoundHttpException {

    /**
     * Entity id
     * @var integer
     */
    protected $id;


    public function __construct(int $id, string $message, \Exception $previous = null, $code = 0) {
        parent::__construct($message, $previous, $code);

        $this->id = $id;
    }


    public function getId() {
        return $this->id;
    }

}
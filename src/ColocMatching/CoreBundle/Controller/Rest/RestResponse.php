<?php

namespace ColocMatching\CoreBundle\Controller\Rest;

use Swagger\Annotations as SWG;

abstract class RestResponse {

    /**
     * @var string
     * @SWG\Property(description="End point of the request")
     */
    protected $link;

    /**
     * @var mixed
     */
    protected $data;


    public function __construct($data, string $link) {
        $this->data = $data;
        $this->link = $link;
    }


    public function getLink() {
        return $this->link;
    }


    public function setLink(string $link = null) {
        $this->link = $link;
        return $this;
    }


    public function getData() {
        return $this->data;
    }


    public function setData($data = null) {
        $this->data = $data;
        return $this;
    }

}
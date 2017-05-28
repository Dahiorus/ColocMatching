<?php

namespace ColocMatching\CoreBundle\Controller\Response;

use Swagger\Annotations as SWG;

abstract class AbstractResponse {

    /**
     * @var string
     * @SWG\Property(description="End point of the request")
     */
    protected $link;

    /**
     * @var mixed
     */
    protected $content;


    /**
     * Constructor
     *
     * @param mixed $content
     * @param string $link
     */
    public function __construct($content, string $link) {
        $this->content = $content;
        $this->link = $link;
    }


    public function getLink() {
        return $this->link;
    }


    public function setLink(?string $link) {
        $this->link = $link;
        return $this;
    }


    public function getContent() {
        return $this->content;
    }


    public function setContent($content = null) {
        $this->content = $content;
        return $this;
    }

}
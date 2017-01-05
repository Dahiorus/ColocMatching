<?php
namespace ColocMatching\CoreBundle\Controller\Rest;

abstract class RestResponse {

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var string
	 */
	protected $link;

	/**
	 * @var array
	 */
	protected $data;


	public function __construct($data, string $link, string $status) {
		$this->data = $data;
		$this->link = $link;
		$this->status = $status;
	}


	public function getStatus() {
		return $this->status;
	}


	public function setStatus(string $status) {
		$this->status = $status;
		return $this;
	}


	public function getLink() {
		return $this->link;
	}


	public function setLink($link) {
		$this->link = $link;
		return $this;
	}


	public function getData() {
		return $this->data;
	}


	public function setData($data) {
		$this->data = $data;
		return $this;
	}

}
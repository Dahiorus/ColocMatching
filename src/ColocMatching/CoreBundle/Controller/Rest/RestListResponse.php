<?php
namespace ColocMatching\CoreBundle\Controller\Rest;

class RestListResponse extends RestResponse {

	/**
	 * @var int
	 */
	private $start = 0;
	
	/**
	 * @var int
	 */
	private $size;
	
	/**
	 * @var int
	 */
	private $total;
	
	/**
	 * @var string
	 */
	private $orderBy;
	
	/**
	 * @var string
	 */
	private $sort;
	
	
	public function __construct(array $data, string $link, string $status = 'success') {
		parent::__construct($data, $link, $status);
		
		$this->size = count($data);
	}
	

	public function getStart() {
		return $this->start;
	}


	public function setStart(int $start) {
		$this->start = $start;
		return $this;
	}

	
	public function getSize() {
		return $this->size;
	}
	
	
	public function setSize(int $size) {
		$this->size = $size;
		return $this;
	}
	

	public function getTotal() {
		return $this->total;
	}


	public function setTotal(int $total) {
		$this->total = $total;
		return $this;
	}


	public function getSort() {
		return $this->sort;
	}


	public function setSort($sort) {
		$this->sort = $sort;
		return $this;
	}


	public function getOrderBy() {
		return $this->orderBy;
	}


	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}
	
}
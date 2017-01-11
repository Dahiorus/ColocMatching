<?php
namespace ColocMatching\CoreBundle\Controller\Rest;

/**
 * Represents a data response container for a list request to the API
 *
 * @author brondon.ung
 */
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
	private $order;
	
	/**
	 * @var string
	 */
	private $sort;
	
	/**
	 * @var string
	 */
	private $next;
	
	/**
	 * @var string
	 */
	private $prev;
	
	
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


	public function getOrder() {
		return $this->order;
	}


	public function setOrder($order) {
		$this->order = $order;
		return $this;
	}


	public function getNext() {
		return $this->next;
	}


	public function setNext($next) {
		$this->next = $next;
		return $this;
	}


	public function getPrev() {
		return $this->prev;
	}


	public function setPrev($prev) {
		$this->prev = $prev;
		return $this;
	}
	
	
	/**
	 * Set previous and next link for this RestListResponse
	 */
	public function setRelationLinks(int $page) {
		$self = $this->link;
		
		if ($page > 1) {
			$prev = preg_replace("/page=\d+/", 'page='.($page-1), $self);
			$this->setPrev($prev);
		}
		
		if ($this->start + $this->size < $this->total) {
			$pageRegEx = "/page=\d+/";
				
			if (preg_match($pageRegEx, $self) > 0) {
				$next = preg_replace($pageRegEx, 'page='.($page+1), $self);
			} else {
				$separator =  (preg_match('/\?/', $self) > 0) ? '&' : '?';
				$next = $self . $separator . 'page=' . ($page+1);
			}
				
			$this->setNext($next);
		}
	}
	
}
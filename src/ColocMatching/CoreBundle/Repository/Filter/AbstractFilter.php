<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;

abstract class AbstractFilter {

	protected $offset = 0;
	
	protected $size = RequestConstants::DEFAULT_LIMIT;
	
	protected $order = RequestConstants::DEFAULT_ORDER;
	
	protected $sort = RequestConstants::DEFAULT_SORT;


	public function getOffset() {
		return $this->offset;
	}


	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}


	public function getSize() {
		return $this->size;
	}


	public function setSize($size) {
		$this->size = $size;
		return $this;
	}


	public function getOrder() {
		return $this->order;
	}


	public function setOrder($order) {
		$this->order = $order;
		return $this;
	}


	public function getSort() {
		return $this->sort;
	}


	public function setSort($sort) {
		$this->sort = $sort;
		return $this;
	}
	
	
	public function __toString() {
		return sprintf(
			"AbstractFilter [offset=%d, size=%d, order='%s', sort='%s']",
			$this->offset, $this->size, $this->order, $this->sort);
	}
	
}
<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Controller\Rest\RequestConstants;

abstract class AbstractFilter {

	protected $offset = 0;
	
	protected $size = RequestConstants::DEFAULT_LIMIT;
	
	protected $orderBy = RequestConstants::DEFAULT_ORDER_BY;
	
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


	public function getOrderBy() {
		return $this->orderBy;
	}


	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
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
			"AbstractFilter [offset=%d, size=%d, orderBy='%s', sort='%s']",
			$this->offset, $this->size, $this->orderBy, $this->sort);
	}
	
}
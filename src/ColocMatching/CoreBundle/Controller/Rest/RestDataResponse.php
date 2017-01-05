<?php
namespace ColocMatching\CoreBundle\Controller\Rest;

class RestDataResponse extends RestResponse {

	public function __construct($data, string $link, string $status = 'success') {
		parent::__construct($data, $link, $status);
	}
	
}
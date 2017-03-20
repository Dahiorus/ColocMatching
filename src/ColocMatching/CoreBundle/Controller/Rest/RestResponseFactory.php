<?php

namespace ColocMatching\CoreBundle\Controller\Rest;

use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for creating RestResponse instances
 *
 * @author Dahiorus
 */
class RestResponseFactory {

    /**
     * @var RequestStack
     */
    private $requestStack;


    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }


    /**
     * Creates a RestDataResponse
     *
     * @param unknown $data
     * @param string $link
     * @return RestDataResponse
     */
    public function createRestDataResponse($data, string $link = null): RestDataResponse {
        if (empty($link)) {
            return new RestDataResponse($data, $this->requestStack->getCurrentRequest()->getUri());
        }

        return new RestDataResponse($data, $link);
    }


    /**
     * Creates a RestListResponse
     *
     * @param array $data
     * @param string $link
     * @param int $total
     * @param AbstractFilter $filter
     * @return RestListResponse
     */
    public function createRestListResponse(array $data, int $total, AbstractFilter $filter): RestListResponse {
        $restList = new RestListResponse($data, $this->requestStack->getCurrentRequest()->getUri());

        $restList->setPage($filter->getPage());
        $restList->setSize($filter->getSize());
        $restList->setOrder($filter->getOrder());
        $restList->setSort($filter->getSort());
        $restList->setTotalElements($total);
        $restList->setRelationLinks();

        return $restList;
    }

}
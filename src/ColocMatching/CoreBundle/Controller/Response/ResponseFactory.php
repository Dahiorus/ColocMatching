<?php

namespace ColocMatching\CoreBundle\Controller\Response;

use ColocMatching\CoreBundle\Entity\EntityInterface;
use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for creating AbstractResponse instances
 *
 * @author Dahiorus
 */
class ResponseFactory {

    /**
     * @var RequestStack
     */
    private $requestStack;


    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }


    /**
     * Creates a EntityResponse
     *
     * @param EntityInterface $data
     * @param string $link
     * @return EntityResponse
     */
    public function createEntityResponse($data, string $link = null): EntityResponse {
        if (empty($link)) {
            return new EntityResponse($data, $this->requestStack->getCurrentRequest()->getUri());
        }

        return new EntityResponse($data, $link);
    }


    /**
     * Creates a PageResponse
     *
     * @param array $content
     * @param string $link
     * @param int $total
     * @param AbstractFilter $filter
     * @return PageResponse
     */
    public function createPageResponse(array $content, int $total, PageableFilter $filter): PageResponse {
        $response = new PageResponse($content, $this->requestStack->getCurrentRequest()->getUri());

        $response->setPage($filter->getPage());
        $response->setSize($filter->getSize());
        $response->setOrder($filter->getOrder());
        $response->setSort($filter->getSort());
        $response->setTotalElements($total);
        $response->setRelationLinks();

        return $response;
    }

}
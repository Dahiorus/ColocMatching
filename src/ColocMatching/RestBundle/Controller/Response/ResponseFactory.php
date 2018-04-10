<?php

namespace ColocMatching\RestBundle\Controller\Response;

use ColocMatching\CoreBundle\Repository\Filter\PageableFilter;
use ColocMatching\CoreBundle\Repository\Filter\Searchable;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for creating AbstractResponse instances
 *
 * @author Dahiorus
 * @deprecated
 */
class ResponseFactory
{
    /**
     * @var RequestStack
     */
    private $requestStack;


    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * Creates a PageResponse
     *
     * @param array $content
     * @param int $total
     * @param PageableFilter|Searchable $filter
     *
     * @return PageResponse
     */
    public function createPageResponse(array $content, int $total, PageableFilter $filter) : PageResponse
    {
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

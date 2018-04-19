<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Form\Type\Filter\PageableFilterType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Service for creating filter class instances
 *
 * @author Dahiorus
 */
class FilterFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    public function __construct(LoggerInterface $logger, FormFactoryInterface $formFactory)
    {
        $this->logger = $logger;
        $this->formFactory = $formFactory;
    }


    /**
     * Creates a filter for pagination purpose
     *
     * @param int $page The page number (from 1)
     * @param int $limit The page size
     * @param string $order The ordering direction ('ASC' or 'DESC')
     * @param string $sort The attribute name to sort the result
     *
     * @return PageableFilter
     */
    public function createPageableFilter(int $page, int $limit, string $order = PageableFilter::ORDER_ASC,
        string $sort = "createdAt") : PageableFilter
    {
        $filter = new PageableFilter();

        $filter->setPage($page);
        $filter->setSize($limit);
        $filter->setOrder($order);
        $filter->setSort($sort);

        return $filter;
    }


    /**
     * Creates a criteria filter from criteria data array for searching purpose
     *
     * @param string $filterTypeClass The class of AbstractFilterType
     * @param Searchable $filter The criteria filter instance to build
     * @param array $filterData The filter data
     *
     * @return Searchable
     * @throws InvalidFormException
     */
    public function buildCriteriaFilter(string $filterTypeClass, Searchable $filter, array $filterData) : Searchable
    {
        /** @var PageableFilterType */
        $filterForm = $this->formFactory->create($filterTypeClass, $filter);

        if (!$filterForm->submit($filterData)->isValid())
        {
            $this->logger->error("Submitted filter data is invalid",
                array ("filter" => $filter, "data" => $filterData, "errors" => $filterForm->getErrors(true, false)));

            throw new InvalidFormException("Invalid filter data submitted", $filterForm->getErrors(true));
        }

        return $filterForm->getData();
    }

}
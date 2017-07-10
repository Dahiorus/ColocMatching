<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\PageableFilterType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Service for creating filter class instances
 *
 * @author Dahiorus
 */
class FilterFactory {

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    public function __construct(FormFactoryInterface $formFactory) {
        $this->formFactory = $formFactory;
    }


    /**
     * Creates a filter for pagination purpose
     *
     * @param int $page     The page number (from 1)
     * @param int $limit    The page size
     * @param string $order The ordering direction ('ASC' or 'DESC')
     * @param string $sort  The attribute name to sort the result
     *
     * @return PageableFilter
     */
    public function createPageableFilter(int $page, int $limit, string $order, string $sort) : PageableFilter {
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
     * @param Searchable $filter      The criteria filter instance to build
     * @param array $filterData       The filter data
     *
     * @return Searchable
     * @throws InvalidFormDataException
     */
    public function buildCriteriaFilter(string $filterTypeClass, Searchable $filter, array $filterData) : Searchable {
        /** @var PageableFilterType */
        $filterForm = $this->formFactory->create($filterTypeClass, $filter);

        if (!$filterForm->submit($filterData)->isValid()) {
            throw new InvalidFormDataException("Invalid filter data submitted", $filterForm->getErrors(true, true));
        }

        return $filterForm->getData();
    }

}
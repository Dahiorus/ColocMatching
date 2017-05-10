<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Exception\InvalidFormDataException;
use ColocMatching\CoreBundle\Form\Type\Filter\AbstractFilterType;
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
     * Sets an AbstractFilter pagination parameters
     *
     * @param AbstractFilter $filter
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param string $sort
     * @return AbstractFilter
     */
    public function setFilter(AbstractFilter $filter, int $page, int $limit, string $order, string $sort): AbstractFilter {
        $filter->setPage($page);
        $filter->setSize($limit);
        $filter->setOrder($order);
        $filter->setSort($sort);

        return $filter;
    }


    /**
     * Creates an AbstractFilter from criteria data array
     *
     * @param string $filterTypeClass The class of AbstractFilterType
     * @param AbstractFilter $filter The filter instance to build
     * @param array $filterData The filter data
     * @throws InvalidFormDataException
     * @return AbstractFilter
     */
    public function buildCriteriaFilter(string $filterTypeClass, AbstractFilter $filter, array $filterData): AbstractFilter {
        /** @var AbstractFilterType */
        $filterForm = $this->formFactory->create($filterTypeClass, $filter);

        if (!$filterForm->submit($filterData)->isValid()) {
            throw new InvalidFormDataException("Invalid filter data submitted", $filterForm->getErrors(true, true));
        }

        return $filterForm->getData();
    }

}
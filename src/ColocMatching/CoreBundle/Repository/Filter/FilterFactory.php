<?php

namespace ColocMatching\CoreBundle\Repository\Filter;

use ColocMatching\CoreBundle\Exception\InvalidFormException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Service for creating filter class instances
 *
 * @author Dahiorus
 * @deprecated
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
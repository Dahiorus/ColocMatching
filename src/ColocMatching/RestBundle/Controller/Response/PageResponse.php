<?php

namespace ColocMatching\RestBundle\Controller\Response;

use ColocMatching\CoreBundle\Repository\Filter\Pageable\Pageable;
use ColocMatching\CoreBundle\Repository\Filter\Pageable\Sort;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * Response for a paginated search request
 *
 * @Serializer\ExclusionPolicy("ALL")
 * @Serializer\AccessorOrder("custom", custom = {"page", "size", "totalPages"})
 *
 * @Hateoas\Relation(
 *   name="first", href = @Hateoas\Route(
 *     name="expr(object.getRoute())", absolute=true, parameters="expr(object.getPagingParameters(1))"),
 *     exclusion = @Hateoas\Exclusion(excludeIf="expr(object.getPage() == 1)")
 * )
 * @Hateoas\Relation(
 *   name="last", href = @Hateoas\Route(
 *     name="expr(object.getRoute())", absolute=true,
 *     parameters="expr(object.getPagingParameters(object.getTotalPages()))"),
 *   exclusion = @Hateoas\Exclusion(excludeIf="expr(object.getTotalPages() <= 1)")
 * )
 * @Hateoas\Relation(
 *   name="prev", href = @Hateoas\Route(
 *     name="expr(object.getRoute())", absolute=true,
 *     parameters="expr(object.getPagingParameters(object.getPage() - 1))"),
 *   exclusion = @Hateoas\Exclusion(excludeIf="expr(!object.hasPrev())")
 * )
 * @Hateoas\Relation(
 *   name="next", href = @Hateoas\Route(
 *     name="expr(object.getRoute())", absolute=true,
 *     parameters="expr(object.getPagingParameters(object.getPage() + 1))"),
 *   exclusion = @Hateoas\Exclusion(excludeIf="expr(!object.hasNext())")
 * )
 *
 * @author Dahiorus
 */
class PageResponse extends CollectionResponse
{
    /**
     * Response page
     * @var integer
     * @Serializer\Expose(if="object.getPage() != null")
     */
    private $page;

    /**
     * Response size
     * @var integer
     * @Serializer\Expose(if="object.getSize() > 0")
     */
    private $size = 0;

    /**
     * Response sorting filter
     * @var array<string, string>
     * @Serializer\Expose
     */
    private $sort = array ();


    public function __construct(array $data, string $route,
        array $routeParameters = array (), Pageable $pageable, int $total = 0)
    {
        parent::__construct($data, $route, $routeParameters, $total);

        $this->page = $pageable->getPage();
        $this->size = $pageable->getSize();

        $sorts = $pageable->getSorts();
        array_walk($sorts, function (Sort $sort) {
            $this->sort[ $sort->getProperty() ] = $sort->getDirection();
        });
    }


    public function __toString()
    {
        return parent::__toString() . "[page=" . $this->page . ", size=" . $this->size . ", count=" . $this->count
            . ", total=" . $this->total . ", sort=" . json_encode($this->sort) . ", hasPrev=" . $this->hasPrev()
            . ", hasNext=" . $this->hasNext() . ", isFirst=" . $this->isFirst() . ", isLast=" . $this->isLast() . "]";
    }


    public function getPage()
    {
        return $this->page;
    }


    public function getSize()
    {
        return $this->size;
    }


    public function getTotal()
    {
        return $this->total;
    }


    public function getSort()
    {
        return $this->sort;
    }


    /**
     * Gets route parameters. Can modify the 'page' parameter to adapt it in the route resolution.
     *
     * @param int|null $page The page value to set in the query parameter 'page'
     *
     * @return array
     */
    public function getPagingParameters(int $page = null)
    {
        if (array_key_exists("page", $this->routeParameters))
        {
            $this->routeParameters["page"] = empty($page) ? $this->routeParameters["page"] : $page;
        }

        return $this->routeParameters;
    }


    /**
     * Response total pages count
     * @return int
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("totalPages"),
     * @Serializer\Exclude(if="object.getTotalPages() == 0"),
     * @Serializer\Type("integer")
     */
    public function getTotalPages() : int
    {
        if ($this->size == 0)
        {
            return 0;
        }

        return (int)ceil($this->total / $this->size);
    }


    public function hasPrev()
    {
        return $this->page > 1;
    }


    public function hasNext()
    {
        return $this->page + 1 <= $this->getTotalPages();
    }


    public function isFirst()
    {
        return !$this->hasPrev();
    }


    public function isLast()
    {
        return !$this->hasNext();
    }

}

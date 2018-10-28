<?php

namespace App\Rest\Controller\Response;

use App\Core\Manager\Page;
use App\Core\Repository\Filter\Pageable\Sort;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * Response for a paginated search request
 *
 * @Serializer\ExclusionPolicy("ALL")
 * @Serializer\AccessorOrder(order = "custom", custom = { "page", "size", "totalPages", "sort" })
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
     * @SWG\Property(property="page", type="integer", example="1", readOnly=true)
     */
    private $page;

    /**
     * Response size
     * @var integer
     * @Serializer\Expose(if="object.getSize() > 0")
     * @SWG\Property(property="size", type="integer", example="20", readOnly=true)
     */
    private $size = 0;

    /**
     * Response sorting filter
     * @var array<string, string>
     * @Serializer\Expose
     * @SWG\Property(property="sort", type="object", additionalProperties=true, example={ "createdAt": "asc" },
     *     readOnly=true)
     */
    private $sort = array ();


    public function __construct(Page $page, string $route, array $routeParameters)
    {
        parent::__construct($page, $route, $routeParameters);

        $this->page = $page->getPage();
        $this->size = $page->getSize();

        $sorts = $page->getSorts();
        array_walk($sorts, function (Sort $sort) {
            $this->sort[ $sort->getProperty() ] = $sort->getDirection();
        });
    }


    public function __toString()
    {
        return parent::__toString() . "[page=" . $this->page . ", size=" . $this->size
            . ", sort=" . json_encode($this->sort) . ", hasPrev=" . $this->hasPrev() . ", hasNext=" . $this->hasNext()
            . ", isFirst=" . $this->isFirst() . ", isLast=" . $this->isLast() . "]";
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

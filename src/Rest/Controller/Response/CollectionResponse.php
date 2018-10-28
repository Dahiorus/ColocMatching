<?php

namespace App\Rest\Controller\Response;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * Response for a paginated search request
 *
 * @Serializer\ExclusionPolicy("ALL")
 * @Serializer\AccessorOrder(order = "custom", custom = { "count", "total" })
 *
 * @Hateoas\Relation(
 *   name="self", href = @Hateoas\Route(
 *     name="expr(object.getRoute())", absolute=true, parameters="expr(object.getRouteParameters())")
 * )
 *
 * @author Dahiorus
 */
class CollectionResponse
{
    /**
     * Response content
     * @var array
     * @Serializer\Expose
     */
    protected $content;

    /**
     * Response content size
     * @var integer
     * @Serializer\Expose
     * @SWG\Property(property="count", type="integer", example="10", readOnly=true)
     */
    protected $count;

    /**
     * Response total count
     * @var integer
     * @Serializer\Expose
     * @SWG\Property(property="total", type="integer", example="100", readOnly=true)
     */
    protected $total;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $routeParameters;


    public function __construct(array $data, string $route,
        array $routeParameters = array (), int $total = null)
    {
        $this->content = $data;
        $this->count = count($data);
        $this->total = $total ?: $this->count;
        $this->route = $route;
        $this->routeParameters = $routeParameters;
    }


    public function __toString()
    {
        return get_class($this) . " [count=" . $this->count . ", total=" . $this->total . "]";
    }


    public function getContent()
    {
        return $this->content;
    }


    public function getCount()
    {
        return $this->count;
    }


    public function getRoute()
    {
        return $this->route;
    }


    public function getRouteParameters()
    {
        return $this->routeParameters;
    }


    public function getParameter(string $name)
    {
        if (!array_key_exists($name, $this->routeParameters))
        {
            return null;
        }

        return $this->routeParameters[ $name ];
    }

}

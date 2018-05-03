<?php

namespace ColocMatching\RestBundle\Controller\Response;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * Response for a paginated search request
 *
 * @Serializer\ExclusionPolicy("ALL")
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
     */
    protected $count;

    /**
     * Response total count
     * @var integer
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

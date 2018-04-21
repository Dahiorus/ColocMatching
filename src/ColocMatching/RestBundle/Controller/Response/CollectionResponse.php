<?php

namespace ColocMatching\RestBundle\Controller\Response;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * Response for a paginated search request
 *
 * @Serializer\ExclusionPolicy("ALL")
 * @SWG\Definition(definition="CollectionResponse")
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
     * @SWG\Property
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
        array $routeParameters = array (), int $total = 0)
    {
        $this->content = $data;
        $this->total = $total;
        $this->count = count($data);
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

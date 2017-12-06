<?php namespace Lit\Bolt\Router;

use FastRoute\RouteCollector;

abstract class FastRouteDefinition
{
    abstract public function __invoke(RouteCollector $routeCollector);
}

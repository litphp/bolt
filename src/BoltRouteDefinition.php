<?php namespace Lit\Bolt;

use FastRoute\RouteCollector;

abstract class BoltRouteDefinition
{
    abstract public function __invoke(RouteCollector $routeCollector);
}

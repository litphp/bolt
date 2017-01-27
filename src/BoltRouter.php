<?php namespace Lit\Bolt;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Lit\CachedFastRoute\CachedDispatcher;
use Lit\CachedFastRoute\FastRouteRouter;
use Lit\Core\Interfaces\IStubResolver;
use Lit\Nexus\Interfaces\ISingleValue;

class BoltRouter extends FastRouteRouter
{
    public function __construct(
        ISingleValue $cache,
        RouteParser $routeParser,
        DataGenerator $dataGenerator,
        callable $routeDefinition,
        $dispatcherClass,
        IStubResolver $stubResolver,
        $notFound,
        $methodNotAllowed = null
    ) {
        /** @noinspection PhpParamsInspection */
        parent::__construct(new CachedDispatcher(
            $cache,
            $routeParser,
            $dataGenerator,
            $routeDefinition,
            $dispatcherClass
        ), $stubResolver, $notFound, $methodNotAllowed);
    }
}

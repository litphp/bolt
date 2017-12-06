<?php namespace Lit\Bolt\Router;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Lit\CachedFastRoute\CachedDispatcher;
use Lit\CachedFastRoute\FastRouteRouter;
use Lit\Core\Interfaces\RouterInterface;
use Lit\Core\Interfaces\RouterStubResolverInterface;
use Lit\Nexus\Void\VoidSingleValue;
use Zend\Diactoros\Response\EmptyResponse;


class FastRouteConfiguration
{
    public static function default()
    {
        return [
            RouterStubResolverInterface::class => ['$' => 'autowire', BoltStubResolver::class],//lit-core

            DataGenerator::class => ['$' => 'autowire', DataGenerator\GroupCountBased::class],//fast-route
            RouteParser::class => ['$' => 'autowire', RouteParser\Std::class],//fast-route
            Dispatcher::class => [
                '$' => 'autowire',
                CachedDispatcher::class,
                [
                    'cache' => new VoidSingleValue(),
                    'routeDefinition' => ['$' => 'alias', FastRouteDefinition::class],
                    'dispatcherClass' => Dispatcher\GroupCountBased::class,
                ]
            ],


            RouterInterface::class => ['$' => 'alias', FastRouteRouter::class],
            FastRouteRouter::class => [
                '$' => 'autowire',
                null,
                [
                    'notFound' => new EmptyResponse(404),
                ]
            ],
        ];
    }

}

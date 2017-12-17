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
            RouterInterface::class => ['$' => 'alias', FastRouteRouter::class],
            FastRouteRouter::class => [
                '$' => 'autowire',
                null,
                [
                    'notFound' => new EmptyResponse(404),
                ]
            ],

            RouterStubResolverInterface::class => ['$' => 'autowire', BoltStubResolver::class],

            DataGenerator::class => ['$' => 'autowire', DataGenerator\GroupCountBased::class],
            RouteParser::class => ['$' => 'autowire', RouteParser\Std::class],
            Dispatcher::class => [
                '$' => 'autowire',
                CachedDispatcher::class,
                [
                    'cache' => new VoidSingleValue(),
                    'routeDefinition' => ['$' => 'alias', FastRouteDefinition::class],
                    'dispatcherClass' => Dispatcher\GroupCountBased::class,
                ]
            ],
        ];
    }

}

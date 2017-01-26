<?php namespace Lit\Bolt;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Lit\CachedFastRoute\CachedDispatcher;
use Lit\Core\Interfaces\IRouter;
use Lit\Core\Interfaces\IStubResolver;
use Lit\Nexus\Traits\DiContainerTrait;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @property EventDispatcherInterface $events
 * @property IStubResolver $stubResolver
 * @property IRouter $router
 * @property BoltApp $app
 * @property PropertyAccessor $accessor
 */
class BoltContainer extends Container
{
    use DiContainerTrait;

    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this[BoltContainer::class] = $this;
        $this
            ->alias(BoltRouter::class, IRouter::class)
            ->alias(BoltStubResolver::class, IStubResolver::class)
            ->provideParameter(CachedDispatcher::class, [
//                'cache' => new VoidSingleValue(),
//                'routeDefinition' => function() {},
                'dispatcherClass' => Dispatcher\GroupCountBased::class,
                DataGenerator::class => DataGenerator\GroupCountBased::class,
                RouteParser::class => RouteParser\Std::class,
            ])
            ->provideParameter(PropertyAccessor::class, [
                'magicCall' => false,
                'throwExceptionOnInvalidIndex' => true,
            ])
            ->alias(PropertyAccessor::class, 'accessor')
            ->alias(BoltRouter::class, 'router')
            ->alias(IStubResolver::class, 'stubResolver')
            ->alias(EventDispatcher::class, 'events');
    }

    function __get($name)
    {
        return $this->offsetGet($name);
    }

    function __isset($name)
    {
        return $this->offsetExists($name);
    }
}

<?php namespace Lit\Bolt;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Lit\Core\Interfaces\IRouter;
use Lit\Core\Interfaces\IStubResolver;
use Lit\Nexus\Traits\DiContainerTrait;
use Lit\Nexus\Void\VoidSingleValue;
use Nimo\Bundled\FixedResponseMiddleware;
use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Zend\Diactoros\Response\EmptyResponse;

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
            ->alias(BoltRouter::class, IRouter::class)//lit-core
            ->alias(BoltStubResolver::class, IStubResolver::class)//lit-core
            ->alias(DataGenerator\GroupCountBased::class, DataGenerator::class)//fast-route
            ->alias(RouteParser\Std::class, RouteParser::class)//fast-route
            ->provideParameter(BoltRouter::class, [
                'cache' => new VoidSingleValue(),
                'routeDefinition' => function () {
                    return $this->produce(BoltRouteDefinition::class);
                },
                'dispatcherClass' => Dispatcher\GroupCountBased::class,
                'notFound' => function () {
                    return $this->protect(new FixedResponseMiddleware(new EmptyResponse(404)));
                },
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

    /**
     * grab $target's $key value, while not available, return $default instead
     * delegate to symfony/property-access
     *
     * @param mixed $target array or object target
     * @param string $key support symfony/property-access key format
     * @param null $default default value while $key is not available
     * @return mixed
     */
    public function get($target, $key, $default = null)
    {
        if(!$this->accessor->isReadable($target, $key)) {
            return $default;
        }

        return $this->accessor->getValue($target, $key);
    }
}

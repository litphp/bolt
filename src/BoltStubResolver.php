<?php namespace Lit\Bolt;

use Lit\Core\Interfaces\IStubResolver;

class BoltStubResolver implements IStubResolver
{
    /**
     * @var BoltContainer
     */
    private $container;

    public function __construct(BoltContainer $container)
    {
        $this->container = $container;
    }

    public function resolve($stub)
    {
        if (is_callable($stub)) {
            return $stub;
        }

        if (is_string($stub) && class_exists($stub)) {
            return $this->container->produce($stub);
        }

        //[$className, $params]
        if (is_array($stub) && count($stub) === 2 && class_exists($stub[0])) {
            return $this->container->instantiate($stub[0], $stub[1]);
        }

        throw new \RuntimeException("cannot understand stub");
    }
}

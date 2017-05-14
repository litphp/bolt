<?php namespace Lit\Bolt;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lit\Core\Interfaces\IStubResolver;
use Psr\Http\Message\ResponseInterface;
use Zend\Stratigility\Middleware\CallableInteropMiddlewareWrapper;

class BoltStubResolver implements IStubResolver
{
    /**
     * @var BoltContainer
     */
    protected $container;
    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    public function __construct(BoltContainer $container)
    {
        $this->container = $container;
    }

    public function resolve($stub)
    {
        if (is_callable($stub)) {
            return new CallableInteropMiddlewareWrapper($stub);
        }

        if($stub instanceof MiddlewareInterface) {
            return $stub;
        }

        $extraParameters = [
            'responsePrototype' => $this->responsePrototype
        ];

        if (is_string($stub) && class_exists($stub)) {
            return $this->container->produce($stub, $extraParameters);
        }

        //[$className, $params]
        if (is_array($stub) && count($stub) === 2 && class_exists($stub[0])) {
            return $this->container->instantiate($stub[0], $stub[1] + $extraParameters);
        }

        throw new \RuntimeException("cannot understand stub");
    }

    /**
     *
     * @param ResponseInterface $responsePrototype
     * @return $this
     */
    public function setResponsePrototype($responsePrototype)
    {
        $this->responsePrototype = $responsePrototype;

        return $this;
    }
}

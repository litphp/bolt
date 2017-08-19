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

        return BoltContainerStub::tryParse($stub)->instantiateFrom($this->container, $extraParameters);
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

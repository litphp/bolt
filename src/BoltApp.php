<?php namespace Lit\Bolt;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Lit\Core\Action;
use Lit\Core\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

class BoltApp extends App
{
    const EVENT_AFTER_LOGIC = 'bolt.app.afterLogic';
    /**
     * @var BoltContainer
     */
    protected $container;

    public function __construct(BoltContainer $container, ResponseInterface $responsePrototype = null)
    {
        if (is_null($responsePrototype)) {
            $responsePrototype = new Response();
        }
        $this->container = $container;

        parent::__construct($container->router, $responsePrototype);

        $this->container[static::class] = $this;
        $this->container->stubResolver->setResponsePrototype($responsePrototype);
        $this->container->provideParameter(Action::class, [
             'responsePrototype' => $responsePrototype,
        ]);
    }

    public static function run(BoltContainer $container)
    {
        $request = ServerRequestFactory::fromGlobals();

        $response = $container->produce(static::class)->process($request, new class implements DelegateInterface{
            public function process(ServerRequestInterface $request)
            {
                throw new \Exception(__METHOD__ . '/' . __LINE__);
            }
        });

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = parent::process($request, $delegate);

        $this->container->events->dispatch(self::EVENT_AFTER_LOGIC, new GenericEvent($this, [
            'request' => $request,
            'response' => $response,
        ]));

        return $response;
    }
}

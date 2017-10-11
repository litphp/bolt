<?php namespace Lit\Bolt;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Lit\Air\Configurator;
use Lit\Air\Factory;
use Lit\Core\Action;
use Lit\Core\App;
use Lit\Nexus\Utilities\Inspector;
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
        Configurator::config($this->container, [
            static::class => $this,
            Action::class.'::' => [
                'responsePrototype' => $responsePrototype,
            ],
        ]);

        parent::__construct($container->router, $responsePrototype);

        $this->container->stubResolver->setResponsePrototype($responsePrototype);
    }

    public static function run(BoltContainer $container)
    {
        Inspector::setGlobalHandler();
        $request = ServerRequestFactory::fromGlobals();

        $response = Factory::of($container)->produce(static::class)->process($request, new class implements DelegateInterface{
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

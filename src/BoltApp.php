<?php namespace Lit\Bolt;

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

    public function __construct(BoltContainer $container)
    {
        parent::__construct($container->router);

        $this->container = $container;
        $this->container[static::class] = $this;

        $this
            ->prepend([$this, 'wrapper'])
//            ->append($this->bolt->produce(JsonRequestMiddleware::class))
            //default error handler?
        ;

    }

    public static function run(BoltContainer $container)
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        /** @noinspection PhpParamsInspection */
        $response = call_user_func($container->produce(static::class), $request, $response);

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    public function wrapper(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);

        $this->container->events->dispatch(self::EVENT_AFTER_LOGIC, new GenericEvent($this, [
            'request' => $request,
            'response' => $response,
        ]));

        return $response;
    }
}

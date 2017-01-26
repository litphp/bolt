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
    const EVENT_AFTER_LOGIC = 'es.app.afterLogic';
    /**
     * @var BoltContainer
     */
    protected $bolt;

    public function __construct(BoltContainer $container = null)
    {
        parent::__construct($container->router);

        $this->bolt = $container;
        $this->bolt[static::class] = $this;

        $this
            ->prepend([$this, 'wrapper'])
//            ->append($this->bolt->produce(JsonRequestMiddleware::class))
            //default error handler?
        ;

    }

    public static function run(BoltContainer $container = null)
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $response = call_user_func(new static($container), $request, $response);

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    public function wrapper(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response = $next($request, $response);

        $this->bolt->events->dispatch(self::EVENT_AFTER_LOGIC, new GenericEvent($this, [
            'request' => $request,
            'response' => $response,
        ]));

        return $response;
    }
}

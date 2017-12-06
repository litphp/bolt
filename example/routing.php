<?php

use FastRoute\RouteCollector;
use Lit\Bolt\BoltAction;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouterApp;
use Lit\Bolt\Router\FastRouteConfiguration;
use Lit\Bolt\Router\FastRouteDefinition;
use Lit\CachedFastRoute\FastRouteRouter;
use Nimo\Handlers\FixedResponseHandler;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;


require(__DIR__ . '/../vendor/autoload.php');

class NotFoundAction extends BoltAction
{
    protected function main(): ResponseInterface
    {
        return $this->json()->render([
            'not' => 'found',
            'method' => $this->request->getMethod(),
            'uri' => $this->request->getUri()->__toString(),
        ])->withStatus(404);
    }
}

BoltRouterApp::run(new BoltContainer([
            FastRouteDefinition::class => function () {
                return new class extends FastRouteDefinition
                {
                    public function __invoke(RouteCollector $routeCollector)
                    {
                        $testJson = new Response\JsonResponse([
                            'test.json' => 'should be this',
                            'bool' => false,
                            'nil' => null,
                        ]);
                        $routeCollector->get('/test.json', FixedResponseHandler::wrap($testJson));
                    }
                };
            },
            FastRouteRouter::class => [
                '$' => 'autowire',
                null,
                [
                    'notFound' => NotFoundAction::class,
                ]
            ],
        ] + FastRouteConfiguration::default())
);
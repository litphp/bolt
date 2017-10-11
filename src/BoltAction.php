<?php namespace Lit\Bolt;

use Lit\Air\Injection\SetterInjector;
use Lit\Core\Action;
use Lit\Core\JsonView;
use Psr\Http\Message\ResponseInterface;

abstract class BoltAction extends Action
{
    const SETTER_INJECTOR = SetterInjector::class;

    /**
     * @var BoltContainer
     */
    protected $container;

    /**
     * @param ResponseInterface $responsePrototype
     * @return $this
     */
    public function injectResponsePrototype(ResponseInterface $responsePrototype)
    {
        $this->responsePrototype = $responsePrototype;

        return $this;
    }


    public function __construct(BoltContainer $container)
    {
        $this->container = $container;
    }

    public function json(): JsonView
    {
        /**
         * @var JsonView $view
         */
        $view = (new JsonView())->setJsonOption(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $view = $this->attachView($view);

        return $view;
    }

    public function redirect($url, $status = 302): ResponseInterface
    {
        return $this->responsePrototype
            ->withHeader('Location', $url)
            ->withStatus($status);
    }

    protected function getBodyParam($key, $default = null)
    {
        return $this->container->access($this->request->getParsedBody(), $key, $default);
    }

    protected function getQueryParam($key, $default = null)
    {
        return $this->container->access($this->request->getQueryParams(), $key, $default);
    }
}

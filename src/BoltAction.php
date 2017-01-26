<?php namespace Lit\Bolt;

use Lit\Core\Action;
use Lit\Core\JsonView;

abstract class BoltAction extends Action
{
    /**
     * @var BoltContainer
     */
    protected $container;

    public function __construct(BoltContainer $container)
    {
        $this->container = $container;
    }

    public function renderJson(array $data = [])
    {
        return $this->renderView(new JsonView(), $data);
    }


    public function redirect($url, $status = 302)
    {
        return $this->response
            ->withHeader('Location', $url)
            ->withStatus($status);
    }

    protected function getBodyParam($key, $default = null)
    {
        $parsedBody = $this->request->getParsedBody();
        if (!$this->container->accessor->isReadable($parsedBody, $key)) {
            return $default;
        }

        return $this->container->accessor->getValue($parsedBody, $key);
    }

    protected function getQueryParam($key, $default = null)
    {
        $query = $this->request->getQueryParams();
        if (!$this->container->accessor->isReadable($query, $key)) {
            return $default;
        }

        return $this->container->accessor->getValue($query, $key);
    }
}

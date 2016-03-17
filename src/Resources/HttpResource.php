<?php

namespace Langsyne\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UriInterface as Uri;
use Langsyne\DataStores\DataStoreInterface as DataStore;
use Langsyne\Validators\ValidatorInterface as Validator;
use Interop\Container\ContainerInterface;

class HttpResource {

    protected $container;
    protected $data = [];
    protected $dataStore;
    protected $links = [];
    protected $validator;

    function __construct(DataStore $dataStore = null, Validator $validator = null) {
        $this->dataStore = $dataStore;
        $this->validator = $validator;
    }

    function __invoke(Request $request, Response $response, array $args) {
        return $this->{strtolower($request->getMethod())}($request, $response, $args);
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getMethods() {
        return ['GET', 'PUT', 'DELETE'];
    }

    public function getProfile() {
        if ($this->validator) {
            return $this->validator->getProfile();
        }
    }

    public function addData($name, $value)
    {
        if (isset($this->data[$name])) {
            if (!is_array($this->data[$name])) {
                $this->data[$name] = [$this->data[$name]];
            }

            $this->data[$name][] = $value;
        } else {
            $this->data[$name] = $value;
        }

        return $this;
    }

    public function addLink($name, $rel = null)
    {
        if (!$rel) {
            $rel = $name;
        }

        $this->links[$rel] = $name;

        return $this;
    }

    public function get(Request $request, Response $response, array $args) {
        if ($this->dataStore) {
            $this->data = $this->dataStore->read($args);
        }

        $renderer = $this->configureRenderer($request->getUri(), $args);

        return $renderer->render($response);
    }

    public function put(Request $request, Response $response, array $args) {
        if (!$this->dataStore) {
            return $response->withStatus(405);
        }
        
        $body = $request->getParsedBody();

        if ($this->validator) {
            $this->validator->validate($body);
        }
        $this->dataStore->write($args, $body);

        return $this->get($request, $response, $args);
    }

    public function delete(Request $request, Response $response, array $args) {
        if (!$this->dataStore) {
            return $response->withStatus(405);
        }

        $this->dataStore->remove($args);

        return $response->withStatus(204);
    }

    protected function configureRenderer(Uri $url, array $args) {
        $renderer = $this->container->get('renderer');
        $router = $this->container->get('router');
        $path = $url->getPath();

        $renderer->setUrl($path);
        $renderer->setData($this->data);

        foreach ($this->links as $rel => $name) {
            $attributes = [];
            $route = $router->getNamedRoute($name);
            $profile = $route->getCallable()->getProfile();

            try {
                $path = $router->pathFor($name, $args);
            } catch (\InvalidArgumentException $e) {
                $path = $route->getPattern();
                $attributes['templated'] = true;
            }

            if ($profile) {
                $attributes['profile'] = $profile;
            }

            $renderer->addLink($rel, $path, $attributes);
        }

        return $renderer;
    }
}

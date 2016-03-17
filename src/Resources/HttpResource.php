<?php

namespace Langsyne\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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

        $url = $request->getUri()->getPath();
        $renderer = $this->container->get('renderer');
        $router = $this->container->get('router');

        $renderer->setUrl($url);
        $renderer->setData($this->data);

        foreach ($this->links as $rel => $name) {
            $attributes = [];
            $route = $router->getNamedRoute($name);
            $profile = $route->getCallable()->getProfile();
            
            try {
                $url = $router->pathFor($name, $args);
            } catch (\InvalidArgumentException $e) {
                $url = $route->getPattern();
                $attributes['templated'] = true;
            }
            
            if ($profile) {
                $attributes['profile'] = $profile;
            }
            
            $renderer->addLink($rel, $url, $attributes);
        }

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
}

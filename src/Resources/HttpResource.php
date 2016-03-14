<?php

namespace Langsyne\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Langsyne\DataStores\DataStoreInterface as DataStore;
use Langsyne\Renderers\RendererInterface as Renderer;
use Langsyne\Validators\ValidatorInterface as Validator;

class HttpResource {

    protected $dataStore;
    protected $renderer;
    protected $validator;

    function __construct(DataStore $dataStore, Renderer $renderer, Validator $validator) {
        $this->dataStore = $dataStore;
        $this->renderer = $renderer;
        $this->validator = $validator;
    }

    function __invoke(Request $request, Response $response, array $args) {
        return $this->{$request->getMethod()}($request, $response, $args);
    }

    public function get(Request $request, Response $response, array $args) {
        return $this->renderer->render($response, $this->dataStore->read($args));
    }

    public function put(Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();
        $this->validator->validate($body);
        $this->dataStore->write($args, $body);

        return $this->get($request, $response, $args);
    }

    public function delete(Request $request, Response $response, array $args) {
        $this->dataStore->remove($args);

        return $response->withStatus(204);
    }
}

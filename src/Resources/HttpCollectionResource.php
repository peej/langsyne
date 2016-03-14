<?php

namespace Langsyne\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Langsyne\DataStores\DataStoreInterface as DataStore;
use Langsyne\Renderers\RendererInterface as Renderer;
use Langsyne\Validators\ValidatorInterface as Validator;
use Slim\Router as Router;

class HttpCollectionResource extends HttpResource {

    protected $itemRouteName;
    protected $router;

    function __construct(
        DataStore $dataStore, Renderer $renderer, Validator $validator,
        Router $router, $itemRouteName
    ) {
        parent::__construct($dataStore, $renderer, $validator);

        $this->router = $router;
        $this->itemRouteName = $itemRouteName;
    }

    public function get(Request $request, Response $response, array $args) {
        return $this->renderer->render($response, $this->dataStore->listing($args));
    }

    public function post(Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();
        $this->validator->validate($body);
        $keys = $this->dataStore->create($args, $body);
        $url = $this->router->pathFor($this->itemRouteName, $keys);

        return $response->withStatus(204)->withHeader('Location', $url);
    }
}

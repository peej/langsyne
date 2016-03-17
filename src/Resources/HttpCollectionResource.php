<?php

namespace Langsyne\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Langsyne\DataStores\DataStoreInterface as DataStore;
use Langsyne\Validators\ValidatorInterface as Validator;

class HttpCollectionResource extends HttpResource {

    protected $itemName;

    /**
     * @param string $itemName
     */
    function __construct($itemName, DataStore $dataStore, Validator $validator = null) {
        parent::__construct($dataStore, $validator);

        $this->itemName = $itemName;
    }

    public function getMethods() {
        return ['GET', 'POST'];
    }

    public function get(Request $request, Response $response, array $args) {
        if (!$this->dataStore) {
            return $response->withStatus(405);
        }

        $data = $this->dataStore->listing($args);
        $renderer = $this->configureRenderer($request->getUri(), $args);
        $router = $this->container->get('router');
        $this->data['count'] = count($data);

        foreach ($data as $key => $value) {
            $url = $router->pathFor($this->itemName, array_merge($args, ['id' => $key]));

            $renderer->addLink($this->itemName, $url, [
                'name' => $key
            ]);
            $renderer->addEmbed($this->itemName, $url, $value);
        }

        return $renderer->render($response);
    }

    public function post(Request $request, Response $response, array $args) {
        if (!$this->dataStore) {
            return $response->withStatus(405);
        }

        $body = $request->getParsedBody();
        $router = $this->container->get('router');

        $this->validator->validate($body);
        $keys = $this->dataStore->create($args, $body);
        $url = $router->pathFor($this->itemName, $keys);

        return $response->withStatus(201)->withHeader('Location', $url);
    }
}

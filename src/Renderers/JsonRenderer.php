<?php

namespace Langsyne\Renderers;

use Psr\Http\Message\ResponseInterface as Response;

class JsonRenderer implements RendererInterface {
    
    public function render(Response $response, array $data) {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-type', 'application/json');
    }

}

<?php

namespace Langsyne\Renderers;

use Psr\Http\Message\ResponseInterface as Response;

class JsonRenderer implements RendererInterface {
    
    private $data;

    public function setUrl($url) {}

    public function setData(array $data) {
        $this->data = $data;
    }

    public function addLink($rel, $url, array $attributes = array()) {}

    public function addEmbed($rel, $url, array $data) {}
    
    public function render(Response $response) {
        $response->getBody()->write(json_encode($this->data));
        return $response->withHeader('Content-type', 'application/json');
    }

}

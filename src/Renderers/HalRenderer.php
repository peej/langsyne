<?php

namespace Langsyne\Renderers;

use Psr\Http\Message\ResponseInterface as Response;
use Nocarrier\Hal;

class HalRenderer implements RendererInterface {

    private $hal;

    public function __construct($curies) {
        $this->hal = new Hal();

        foreach ($curies as $curie) {
            $this->hal->addCurie($curie['name'], $curie['href']);
        }
    }

    public function setUrl($url) {
        $this->hal->setUri($url);
    }

    public function setData(array $data) {
        $this->hal->setData($data);
    }

    public function addLink($rel, $url, array $attributes = array()) {
        $this->hal->addLink($rel, $url, $attributes);
    }

    public function addEmbed($rel, $url, array $data) {
        $this->hal->addResource($rel, new Hal($url, $data));
    }
    
    public function render(Response $response) {
        $response->getBody()->write($this->hal->asJson(true));
        return $response->withHeader('Content-type', 'application/hal+json');
    }

}

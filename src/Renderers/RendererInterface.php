<?php

namespace Langsyne\Renderers;

use Psr\Http\Message\ResponseInterface as Response;

interface RendererInterface {

    public function setUrl($url);

    public function setData(array $data);

    public function addLink($rel, $url, array $attributes = array());

    public function addEmbed($rel, $url, array $data);

    public function render(Response $response);

}

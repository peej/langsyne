<?php

namespace Langsyne\Renderers;

use Psr\Http\Message\ResponseInterface as Response;

interface RendererInterface {

    public function render(Response $response, array $data);

}

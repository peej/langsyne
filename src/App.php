<?php

namespace Langsyne;

use Langsyne\Resources\HttpResource;

class App extends \Slim\App {
    
    /**
     * @param string $name
     * @param string $path
     * @param HttpResource $resource
     * @return HttpResource
     */
    public function addResource($name, $path, HttpResource $resource)
    {
        $resource->setContainer($this->getContainer());
        $this->map($resource->getMethods(), $path, $resource)->setName($name);

        return $resource;
    }
}

<?php

namespace spec\Langsyne;

use Interop\Container\ContainerInterface;
use Langsyne\Resources\HttpResource;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Router;
use Slim\Route;

class AppSpec extends ObjectBehavior
{
    function it_should_allow_resources_to_be_added(
        HttpResource $resource,
        ContainerInterface $container,
        Router $router,
        Route $route)
    {
        $this->beConstructedWith($container);
        $route->setContainer($container)->shouldBeCalled();
        $route->setOutputBuffering(false)->shouldBeCalled();
        $route->setName('name')->shouldBeCalled();
        $router->map(['GET'], '/path', $resource)->willReturn($route);
        $container->get('router')->willReturn($router);
        $container->get('settings')->willReturn(['outputBuffering' => false]);

        $resource->setContainer($container)->shouldBeCalled();
        $resource->getMethods()->willReturn(['GET']);

        $this->addResource('name', '/path', $resource)->shouldReturn($resource);
    }
}

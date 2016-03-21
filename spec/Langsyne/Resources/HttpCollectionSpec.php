<?php

namespace spec\Langsyne\Resources;

use Interop\Container\ContainerInterface;
use Langsyne\DataStores\DataStoreInterface;
use Langsyne\Validators\ValidatorInterface;
use Langsyne\Renderers\RendererInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UriInterface as Uri;
use Slim\Interfaces\RouterInterface;

class HttpCollectionSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        DataStoreInterface $dataStore,
        RendererInterface $renderer,
        RouterInterface $router,
        ValidatorInterface $validator)
    {
        $container->get('renderer')->willReturn($renderer);
        $container->get('router')->willReturn($router);

        $this->beConstructedWith('item', $dataStore, $validator);
    }

    function it_should_return_the_http_methods_it_supports()
    {
        $this->getMethods()->shouldContain('GET');
        $this->getMethods()->shouldContain('POST');
    }

    function it_should_support_the_GET_method(
        ContainerInterface $container,
        DataStoreInterface $dataStore,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        RouterInterface $router,
        Uri $uri)
    {
        $data = [
            1 => [
                'name' => 'One'
            ],
            2 => [
                'name' => 'Two'
            ]
        ];

        $dataStore->listing([])->willReturn($data);
        $uri->getPath()->willReturn('/item');
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item')->shouldBeCalled();
        $renderer->addLink('item', '/item/1', ['name' => 1])->shouldBeCalled();
        $renderer->addLink('item', '/item/2', ['name' => 2])->shouldBeCalled();
        $renderer->addEmbed('item', '/item/1', $data[1])->shouldBeCalled();
        $renderer->addEmbed('item', '/item/2', $data[2])->shouldBeCalled();
        $renderer->setData([])->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();
        $router->pathFor('item', ['id' => 1])->willReturn('/item/1');
        $router->pathFor('item', ['id' => 2])->willReturn('/item/2');

        $this->setContainer($container);
        $this->get($request, $response, []);
    }

    function it_should_error_when_GET_method_and_no_datastore(
        Request $request,
        Response $response)
    {
        $response->withStatus(405)->shouldBeCalled();

        $this->beConstructedWith('item');
        $this->get($request, $response, []);
    }

    function it_should_support_the_POST_method(
        ContainerInterface $container,
        DataStoreInterface $dataStore,
        Request $request,
        Response $response,
        RouterInterface $router,
        ValidatorInterface $validator)
    {
        $data = [
            'name' => 'Three'
        ];

        $dataStore->create([], $data)->shouldBeCalled()->willReturn([
            'id' => 3
        ]);
        $request->getParsedBody()->willReturn($data);
        $response->withStatus(201)->shouldBeCalled()->willReturn($response);
        $response->withHeader('Location', '/item/3')->shouldBeCalled()->willReturn($response);
        $router->pathFor('item', ['id' => 3])->willReturn('/item/3');
        $validator->validate($data)->shouldBeCalled();

        $this->setContainer($container);
        $this->post($request, $response, []);
    }

}

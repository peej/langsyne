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
use Slim\Route;

class HttpResourceSpec extends ObjectBehavior
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

        $dataStore->read(['id' => 1])->willReturn([]);

        $validator->getProfile()->willReturn('test-profile');

        $this->beConstructedWith($dataStore, $validator);
    }

    function it_should_return_the_http_methods_it_supports()
    {
        $this->getMethods()->shouldContain('GET');
        $this->getMethods()->shouldContain('PUT');
        $this->getMethods()->shouldContain('DELETE');
    }

    function it_should_return_the_profile_it_supports()
    {
        $this->getProfile()->shouldReturn('test-profile');
    }

    function it_should_return_null_if_no_validator()
    {
        $this->beConstructedWith();
        $this->getProfile()->shouldReturn(null);
    }

    function it_should_add_data_with_a_fluid_interface()
    {
        $this->addData('name', 'value')->shouldReturn($this);
    }

    function it_should_add_a_link_with_a_fluid_interface()
    {
        $this->addLink('name', 'rel')->shouldReturn($this);
    }

    function it_should_support_the_GET_method(
        ContainerInterface $container,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        Uri $uri)
    {
        $uri->getPath()->willReturn('/item/1');
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item/1')->shouldBeCalled();
        $renderer->setData([])->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();

        $this->setContainer($container);
        $this->get($request, $response, ['id' => 1]);
    }

    function it_should_add_data_to_the_renderer(
        ContainerInterface $container,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        Uri $uri)
    {
        $uri->getPath()->willReturn('/item/1');
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item/1')->shouldBeCalled();
        $renderer->setData(['name' => 'value'])->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();

        $this->setContainer($container);
        $this->addData('name', 'value');
        $this->get($request, $response, ['id' => 1]);
    }

    function it_should_add_links_to_the_renderer(
        ContainerInterface $container,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        Route $route,
        RouterInterface $router,
        Uri $uri)
    {
        $uri->getPath()->willReturn('/item/1');
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item/1')->shouldBeCalled();
        $renderer->setData([])->shouldBeCalled();
        $renderer->addLink('rel', '/path', ['profile' => 'test-profile'])->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();
        $router->getNamedRoute('name')->willReturn($route);
        $router->pathFor('name', ['id' => 1])->willReturn('/path');
        $route->getCallable()->willReturn($this);

        $this->setContainer($container);
        $this->addLink('name', 'rel');
        $this->get($request, $response, ['id' => 1]);
    }

    function it_should_add_a_templated_link_to_the_renderer(
        ContainerInterface $container,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        Route $route,
        RouterInterface $router,
        Uri $uri)
    {
        $uri->getPath()->willReturn('/item/1');
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item/1')->shouldBeCalled();
        $renderer->setData([])->shouldBeCalled();
        $renderer->addLink('rel', '/path/{var}', [
            'profile' => 'test-profile',
            'templated' => true
        ])->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();
        $router->getNamedRoute('name')->willReturn($route);
        $router->pathFor('name', ['id' => 1])->willThrow('InvalidArgumentException');
        $route->getPattern()->willReturn('/path/{var}');
        $route->getCallable()->willReturn($this);

        $this->setContainer($container);
        $this->addLink('name', 'rel');
        $this->get($request, $response, ['id' => 1]);
    }

    function it_should_support_the_PUT_method(
        ContainerInterface $container,
        DataStoreInterface $dataStore,
        RendererInterface $renderer,
        Request $request,
        Response $response,
        ValidatorInterface $validator,
        Uri $uri)
    {
        $body = [
            'name' => 'test'
        ];

        $uri->getPath()->willReturn('/item/1');
        $request->getParsedBody()->willReturn($body);
        $request->getUri()->willReturn($uri);
        $renderer->setUrl('/item/1')->shouldBeCalled();
        $renderer->setData($body)->shouldBeCalled();
        $renderer->render($response)->shouldBeCalled();
        $validator->validate($body)->shouldBeCalled();
        $dataStore->read(['id' => 1])->willReturn($body);
        $dataStore->write(['id' => 1], $body)->shouldBeCalled();

        $this->setContainer($container);
        $this->put($request, $response, ['id' => 1]);
    }

    function it_should_error_when_PUT_method_and_no_datastore(
        Request $request,
        Response $response)
    {
        $response->withStatus(405)->shouldBeCalled();

        $this->beConstructedWith();
        $this->put($request, $response, ['id' => 1]);
    }

    function it_should_support_the_DELETE_method(
        ContainerInterface $container,
        DataStoreInterface $dataStore,
        Request $request,
        Response $response)
    {
        $dataStore->remove(['id' => 1])->shouldBeCalled();
        $response->withStatus(204)->shouldBeCalled();

        $this->setContainer($container);
        $this->delete($request, $response, ['id' => 1]);
    }

    function it_should_error_when_DELETE_method_and_no_datastore(
        Request $request,
        Response $response)
    {
        $response->withStatus(405)->shouldBeCalled();

        $this->beConstructedWith();
        $this->delete($request, $response, ['id' => 1]);
    }

}

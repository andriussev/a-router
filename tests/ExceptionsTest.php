<?php
use Andriussev\ARouter\Exception\MethodInvalidException;
use Andriussev\ARouter\Exception\NamedRouteNotFoundException;
use Andriussev\ARouter\Exception\RouteNotFoundException;
use Andriussev\ARouter\Exception\URLGenerationInvalidException;
use Andriussev\ARouter\Helper;
use Andriussev\ARouter\Router;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase {

    public function testInvalidMethod() {
        $this->expectException(MethodInvalidException::class);

        $router = new Router();

        $router->addRoute('INVALID', '/', function () {
            echo("GET /");
        });
    }

    public function testRouteNotFound() {
        $this->expectException(RouteNotFoundException::class);

        $router = new Router();

        $router->addRoute('GET', '/', function () {
            echo("GET /");
        });

        $router->start('GET', '/non-existing');
    }

    public function testNamedRouteNotFound() {
        $this->expectException(NamedRouteNotFoundException::class);

        $router = new Router();

        $router->addRoute('GET', '/', function () {
            echo("GET /");
        })->setName('index');


        ob_start();
        $router->start('GET', '/');
        $out = ob_get_contents();

        Helper::getUrlToRoute('non-existing-named-route');

    }

    public function testURLGenerationInvalid() {

        $this->expectException(URLGenerationInvalidException::class);

        $router = new Router();

        $router->addRoute('GET', '/books/:id', function ($id) {
            echo("GET /books/" . $id);
        })->setName('singleBook');


        ob_start();
        $router->start('GET', '/books/1');
        $out = ob_get_contents();

        Helper::getUrlToRoute('singleBook');
    }
}
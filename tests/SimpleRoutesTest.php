<?php
use Andriussev\ARouter\Router;
use PHPUnit\Framework\TestCase;

class SimpleRoutesTest extends TestCase {

    public function testRoutes1() {

        /*
         * Test starting routes
         */
        $router = new Router();

        $router->addRoute('GET', '/books', function () {
            echo("GET /books");
        });

        $router->addRoute('GET', '/books/:id', function ($id) {
            echo("GET /books/" . $id);
        });

        ob_start();
        $router->start('GET', '/books/');
        $out = ob_get_contents();
        $this->assertEquals('GET /books', $out);


        ob_start();
        $router->start('GET', '/books/1');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/1', $out);

    }

    public function testRoutes1Reverse() {

        /*
         * Test same routes in reverse
         */
        $router = new Router();

        $router->addRoute('GET', '/books/:id', function ($id) {
            echo("GET /books/" . $id);
        });

        $router->addRoute('GET', '/books', function () {
            echo("GET /books");
        });


        ob_start();
        $router->start('GET', '/books/');
        $out = ob_get_contents();
        $this->assertEquals('GET /books', $out);


        ob_start();
        $router->start('GET', '/books/1');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/1', $out);
    }

    /**
     *  Test that routes coming first are matched firsts
     */
    public function testRouteSequencing() {

        /*
         * Test same routes in reverse
         */
        $router = new Router();

        $router->addRoute('GET', '/books/test', function () {
            echo("GET /books/test");
        });

        $router->addRoute('GET', '/books/:id', function ($id) {
            echo("GET /books/id:" . $id);
        });

        $router->addRoute('GET', '/books/toast', function ($id) {
            echo("GET /books");
        });

        ob_start();
        $router->start('GET', '/books/test');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/test', $out);


        ob_start();
        $router->start('GET', '/books/toast');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/id:toast', $out);

    }

    /**
     * A bit more complex test with multiple routes
     */
    public function testComplex() {

        $router =  new Router();

        $router->addRoute('GET','/books',function() {
            echo ("GET /books");
        });

        $router->addRoute('GET','/books/new',function() {
            echo ("GET /books/new");
        });

        $router->addRoute('GET','/books/:id',function($id) {
            echo ("GET /books/id:".$id);
        });

        $router->addRoute('GET','/books/new/save',function() {
            echo ("GET /books/new/save");
        });

        $router->addRoute('GET','/books/:id/:chapter',function($id, $chapter) {
            echo ("GET /books/$id/$chapter");
        });

        ob_start();
        $router->start('GET', '/books');
        $out = ob_get_contents();
        $this->assertEquals('GET /books', $out);

        ob_start();
        $router->start('GET', '/books/');
        $out = ob_get_contents();
        $this->assertEquals('GET /books', $out);

        ob_start();
        $router->start('GET', '/books/new');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/new', $out);

        ob_start();
        $router->start('GET', '/books/1');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/id:1', $out);

        ob_start();
        $router->start('GET', '/books/new/save');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/new/save', $out);

        ob_start();
        $router->start('GET', '/books/1/2');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/1/2', $out);
    }
}
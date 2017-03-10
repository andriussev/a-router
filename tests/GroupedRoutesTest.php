<?php
use Andriussev\ARouter\Router;
use PHPUnit\Framework\TestCase;

class GroupedRoutesTest extends TestCase {
    
    /**
     * Functionality of grouping routes
     */
    public function testGroups1() {
        
        $router =  new Router();
        
        $router->addRoute('GET','/',function() {
            echo ("GET /");
        });
        
        $books = $router->addGroup('/books');

        $router->addRoute('GET','/',function() {
            echo ("GET /books");
        })->setGroup($books);

        $router->addRoute('GET','/new',function() {
            echo ("GET /books/new");
        })->setGroup($books);

        $router->addRoute('GET','/:id',function($id) {
            echo ("GET /books/id:".$id);
        })->setGroup($books);

        $router->addRoute('GET','/new/save',function() {
            echo ("GET /books/new/save");
        })->setGroup($books);

        $router->addRoute('GET','/:id/:chapter',function($id, $chapter) {
            echo ("GET /books/$id/$chapter");
        })->setGroup($books);
        
        

        ob_start();
        $router->start('GET', '/');
        $out = ob_get_contents();
        $this->assertEquals('GET /', $out);
        

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
    
    /**
     * Functionality of grouping routes + placeholders in group name
     */
    public function testGroups2() {
        
        $router =  new Router();
        
        $router->addRoute('GET','/',function() {
            echo ("GET /");
        });
        
        $books = $router->addGroup('/books');

        $router->addRoute('GET','/',function() {
            echo ("GET /books");
        })->setGroup($books);

        $router->addRoute('GET','/new',function() {
            echo ("GET /books/new");
        })->setGroup($books);
        
        $booksSingle = $router->addGroup('/books/:id');

        $router->addRoute('GET','/',function($id) {
            echo ("GET /books/id:".$id);
        })->setGroup($booksSingle);
        
        $router->addRoute('GET','/save',function($id) {
            echo ("GET /books/id:".$id."/save");
        })->setGroup($booksSingle);
        

        ob_start();
        $router->start('GET', '/');
        $out = ob_get_contents();
        $this->assertEquals('GET /', $out);
        

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
        $router->start('GET', '/books/1/save');
        $out = ob_get_contents();
        $this->assertEquals('GET /books/id:1/save', $out);

    }
}
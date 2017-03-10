<?php
use Andriussev\ARouter\Router;
use Andriussev\ARouter\Helper;
use PHPUnit\Framework\TestCase;

class NamesRoutesTest extends TestCase {
    
    /**
     * Functionality of grouping routes
     */
    public function testNamedRoutes() {
        
        $router =  new Router();
        
        $router->normalizeNamedBeforeFindingRoute();
        
        $router->addRoute('GET','/',function() {
            echo ("GET /");
        })->setName('index');
        
        $books = $router->addGroup('/books');

        $router->addRoute('GET','/',function() {
            echo ("GET /books");
        })->setGroup($books)->setName('allBooks');

        $router->addRoute('GET','/new',function() {
            echo ("GET /books/new");
        })->setGroup($books)->setName('newBook');

        $router->addRoute('GET','/:id',function($id) {
            echo ("GET /books/id:".$id);
        })->setGroup($books)->setName('singleBook');

        $router->addRoute('GET','/new/save',function() {
            echo ("GET /books/new/save");
        })->setGroup($books)->setName('saveBook');

        $router->addRoute('GET','/:id/:chapter',function($id, $chapter) {
            echo ("GET /books/$id/$chapter");
        })->setGroup($books)->setName('singleBookWithChapter');
        
        

        ob_start();
        $router->start('GET', '/');
        $out = ob_get_contents();
        
        $linkToIndex = Helper::getUrlToRoute('index');
        $linkToAllBooks = Helper::getUrlToRoute('allBooks');
        $linkToNewBook = Helper::getUrlToRoute('newBook');
        $linkToSingleBook = Helper::getUrlToRoute('singleBook',['id'=>42]);
        $linkToSaveBook = Helper::getUrlToRoute('saveBook');
        $linkToSingleBookWithChapter = Helper::getUrlToRoute('singleBookWithChapter',['id'=>42,'chapter'=>1337]);
        
        
        $this->assertEquals('/', $linkToIndex);
        $this->assertEquals('/books', $linkToAllBooks);
        $this->assertEquals('/books/new', $linkToNewBook);
        $this->assertEquals('/books/42', $linkToSingleBook);
        $this->assertEquals('/books/new/save', $linkToSaveBook);
        $this->assertEquals('/books/42/1337', $linkToSingleBookWithChapter);
        
        
    }
}
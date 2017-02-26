<?php
require('autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');


/*
 * Small example
 */

$router = new \Router\Router();

$router->addRoute('GET','/books',function() {
    echo ("GET /books");
});
$router->addRoute('GET','/books/:id',function($id) {
    echo ("GET /books/id?");
});
$router->addRoute('GET','/books/:id/:chapter',function($id, $chapter) {
    echo ("GET /books/$id/$chapter");
});
$router->addRoute('GET','/books/new',function() {
    echo ("GET /books/new");
});
$router->addRoute('GET','/books/new/save',function() {
    echo ("GET /books/new/save");
});


$router->start();
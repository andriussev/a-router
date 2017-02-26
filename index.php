<?php
require('autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

$router = new \Router\Router();

$router->addRoute('GET','/books',function() {
    echo ("GET /books");
});
$router->addRoute('GET','/books/:id',function($id) {
    var_dump('id: ' . $id);die();
    echo ("GET /books/id?");
});
$router->addRoute('GET','/books/:id/:bbd',function($id, $bbd) {
    echo ("GET /books/$id/$bbd");
});
$router->addRoute('GET','/books/new',function() {
    echo ("GET /books/new");
});
$router->addRoute('GET','/books/new/save',function() {
    echo ("GET /books/new/save");
});


$router->start();
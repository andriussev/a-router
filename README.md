# a-router

A very simple router (for now) for PHP Web applications.



# Walkthrough

## Installable via Composer:

````composer require "andriussev/a-router":"dev-master" ````

## Usage

### Router object
Assuming that Composer autoloader is in place, a new Router object can be created:

```` $router =  new \Andriussev\ARouter\Router(); ````


### Adding routes

```` $router->addRoute(HTTP_METHOD, ENDPOINT, CALLBACK) ````

The callback will be called after a route is matched.

Example:
```` 
$router->addRoute('GET','/books',function() {
    echo ("GET /books");
}); 
````

### Starting the router

To actually start the application, the router needs to be started.
Simple as that:

```` $router->start(); ````

### Placeholders in routes

All placeholders must be set with a colon in the front, ex: ````:value````

As seen in the example below, the placeholder value will be inserted to the callback function.

Example:
```` 
$router->addRoute('GET','/books/:id',function($id) {
    echo ("GET /books/".$id);
}); 
````

### Gotchas

#### Sequencing
Due to the nature of matching, the route adding is important.
If multiple routes are defined that would match the same route, the one added first, will be triggered.

Example 1:
```` 
$router->addRoute('GET','/books/new',function() {
    echo ("GET /books/new");
}); 
$router->addRoute('GET','/books/:1',function($id) {
    echo ("GET /books/".$id);
}); 
````

Route ````/books/new```` will be matched successfully.

Example 2:
```` 
$router->addRoute('GET','/books/:1',function($id) {
    echo ("GET /books/".$id);
}); 
$router->addRoute('GET','/books/new',function() {
    echo ("GET /books/new");
}); 
````

Route ````/books/new```` will be **never** be matched.
The first route with $id='new' will always trigger first.




# TODO  

* ~~Update to a composer package~~.
* ~~More in-depth documentation~~.
* Route naming.
* Route grouping.
* Handling of before/after.
* ~~Testing~~.
* Custom exceptions and error handling.
* Helper object/methods to build URLs in application.
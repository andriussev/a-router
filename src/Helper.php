<?php namespace Andriussev\ARouter;

use Andriussev\ARouter\Exception\NamedRouteNotFoundException;

class Helper {
    
    /**
     * The router for this helper
     * @var Router
     */
    private static $router;
    
    /**
     * MatchedRouter object for the request
     * @var MatchedRoute
     */
    private static $matchedRoute;
    
    public static function setMatchedRoute(MatchedRoute $matchedRoute) {
        self::$matchedRoute = $matchedRoute;
    }
    
    public static function getRequestValues() {
        return self::$matchedRoute->getMatchedPlaceholders();
    }
    
    public static function setRouter(Router $router) {
        self::$router = $router;
    }

    /**
     * Parses the route url by a given route name
     * @param string $routeName Input route name for an existing named route
     * @param array $routeValues Values for placeholders
     * @return
     * @throws NamedRouteNotFoundException
     */
    public static function getUrlToRoute($routeName, $routeValues = []) {
        $routes = self::$router->getMapByName();
        if(!array_key_exists($routeName,$routes)) {
            throw new NamedRouteNotFoundException('Route with a name \'' . $routeName . '\' not found');
        }
        
        return $routes[$routeName]->generateUrl($routeValues);
    }
}
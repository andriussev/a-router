<?php namespace Andriussev\ARouter;

class Helper {
    
    /**
     * The router for this helper
     * @var Router
     */
    private static $router;
    
    /**
     * MatchedRouter object for the request
     * @var MatchedRouter
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
     */
    public static function getUrlToRoute($routeName, $routeValues = []) {
        $routes = self::$router->getMapByName();
        if(!array_key_exists($routeName,$routes)) {
            throw new \Exception('Route with a name \' . $routeName . \' not found');
        }
        
        return $routes[$routeName]->generateUrl($routeValues);
    }
}
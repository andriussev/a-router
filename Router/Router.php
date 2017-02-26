<?php
namespace Router;

class Router {
    
    public static $placeholderValue = '__PH__';
    
    private $routeValidator = null;
    
    private $map = [];
    
    private $requestMethod = null;
    private $requestUrl = null;
    private $requestUrlParts = [];
    
    public function __construct()
    {
        $this->routeValidator = new \Router\RouteValidator();
    }


    /*
     *  Starts the router. This resolves the request.
     */
    public function start()
    {
        $this->beforeStart();
        
        $route = $this->findRoute();
        
        $this->resolveRoute($route);
        
        $this->afterStart();
    }
    
    private function beforeStart()
    {
        return;
    }
    
    private function afterStart()
    {
        return;
    }
    
    
    /*
     *  Adds a new route to the router
     */
    public function addRoute($method, $endpoint, $action)
    {
        $method = strtoupper($method);
        
        
        /* 
         * Validate input 
         */
        if(!$this->routeValidator->isValidMethod($method)) {
            throw new \Exception('Method not allowed');
        }
        
        if(!$this->routeValidator->isValidEndpoint($endpoint)) {
            throw new \Exception('Endpoint is not valid');
        }
        
        
        /*
         * Create a new route
         */
         $route = new Route();
         $route->setMethod($method);
         $route->setEndpoint($endpoint);
         $route->setAction($action);
         
         /*
          * Add route to array
          */
         $this->mergeNewRouteToMap($route);
         
         return $route;
    }
    
    private function mergeNewRouteToMap($route)
    {
        $method = $route->getMethod();
        if(!array_key_exists($method,$this->map)) {
            $this->map[$method] = [];
        }
        
        $routeParts = $route->getNormalizedEndpointParts();
        
        $currentLevel = &$this->map;
        $previous = $route->getMethod();
        foreach($routeParts as $part) {
            $currentLevel = &$currentLevel[$previous];
            if(!array_key_exists($part,$currentLevel)) {
                $currentLevel[$part] = [];
            }
            // $currentLevel = &$currentLevel[$part];
            $previous = $part;
        }
        $currentLevel[$previous][] = $route;
        
    }
    
    public function debugGetDefinedRoutes()
    {
        return $this->map;
    }
    
    private function findRoute()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REDIRECT_URL'];
        $requestUrlParts = explode("/",$requestUrl);
        $requestUrlPartCount = count($requestUrlParts);
        
        $this->requestMethod = $requestMethod;
        $this->requestUrl = $requestUrl;
        $this->requestUrlParts = $requestUrlParts;
        
        /*
         * Check if any route has the requested method
         */
        if(!array_key_exists($requestMethod,$this->map)) {
            throw new \Exception('Route not found');
        }
        
        
        
        $currentSearchLevel = $this->map[$requestMethod];
        $currentLinearSearchLevel = $requestMethod;
        
        // Search every request URL part
        foreach($requestUrlParts as $i => $part) {
            //echo ("<br><br>Searching for '{$part}' <br>");
            
            // Skip if empty
            if(empty($part)) {
                continue;
            }
            
            //echo "Checking in " . $currentLinearSearchLevel . "<br>";
            // Specific part of the route was found in map
            if(array_key_exists($part,$currentSearchLevel)) {
                // Use that part of the map further on
                $currentSearchLevel = $currentSearchLevel[$part];
                $currentLinearSearchLevel .= "->$part";
                //echo ("Counts needed: {$requestUrlPartCount} Counts searched " .($i+1). " <br>");
                
                // Check if we are at the end of the request parts
                if($requestUrlPartCount == $i+1) {
                    // If we are at the end, we check if there is a non-array element.
                    // If we find one, that means that there is a route that ends here
                    foreach($currentSearchLevel as $k => $levelRouteMap) {
                        if(!is_array($levelRouteMap)) {
                            return $levelRouteMap;
                            die('found!!! ' . json_encode($levelRouteMap));
                        }
                        throw new \Exception('Route not found -1');
                        
                    }
                }
                //echo ("Going deeper " . $currentLinearSearchLevel." <br>");
                
            } 
            // If we don't have a specific part, it could be a placeholder
            else if(array_key_exists(self::$placeholderValue,$currentSearchLevel)) {
                    // Use that part of the map further on
                    $currentSearchLevel = $currentSearchLevel[self::$placeholderValue];
                    $currentLinearSearchLevel .= "->".self::$placeholderValue;
                    //echo ("Counts needed: {$requestUrlPartCount} Counts searched " .($i+1). " <br>");
                    
                    // Check if we are at the end of the request parts
                    if($requestUrlPartCount == $i+1) {
                        // If we are at the end, we check if there is a non-array element.
                        // If we find one, that means that there is a route that ends here
                        foreach($currentSearchLevel as $k => $levelRouteMap) {
                            if(!is_array($levelRouteMap)) {
                                return $levelRouteMap;
                                die('found!!! ' . json_encode($levelRouteMap));
                            }
                            throw new \Exception('Route not found -1');
                            
                        }
                    }
                    //echo ("Going deeper " . $currentLinearSearchLevel . " <br>");
                }
            else {
                throw new \Exception('Route not found 0');
            }
        }
        
        throw new \Exception('Route not found');
    }
    
    private function resolveRoute($route)
    {
        $route->call($this->requestUrlParts);
    }
}
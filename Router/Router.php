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


    /**
     *  Starts the router. This resolves the request.
     */
    public function start()
    {
        $this->beforeStart();

        $route = $this->findRoute();

        $this->resolveRoute($route);

        $this->afterStart();
    }

    /**
     *  Actions to perform before starting
     */
    private function beforeStart()
    {
        return;
    }

    /**
     * Actions to perform after starting
     */
    private function afterStart()
    {
        return;
    }


    /**
     *  Adds a new route to the router
     * @param $method   string HTTP method
     * @param $endpoint string URL endpoint
     * @param $action   callable Function to call if route is matched
     * @return Route
     * @throws \Exception
     */
    public function addRoute($method, $endpoint, $action)
    {
        $method = strtoupper($method);

        // Validate if method allowed
        if (!$this->routeValidator->isValidMethod($method))
        {
            throw new \Exception('Method not allowed');
        }

        // Validate if endpoint is valid
        if (!$this->routeValidator->isValidEndpoint($endpoint))
        {
            throw new \Exception('Endpoint is not valid');
        }


        // Create a new route object
        $route = new Route();
        $route->setMethod($method);
        $route->setEndpoint($endpoint);
        $route->setAction($action);

        // Add newly created route to array
        $this->mergeNewRouteToMap($route);

        // Return route for further chaining
        return $route;
    }

    /**
     * Adds newly created route to a map of all routes
     * @param $route Route
     */
    private function mergeNewRouteToMap(Route $route)
    {
        $method = $route->getMethod();
        if (!array_key_exists($method, $this->map))
        {
            $this->map[$method] = [];
        }

        $routeParts = $route->getNormalizedEndpointParts();

        $currentLevel = &$this->map;
        $previous = $route->getMethod();
        foreach ($routeParts as $part)
        {
            $currentLevel = &$currentLevel[$previous];
            if (!array_key_exists($part, $currentLevel))
            {
                $currentLevel[$part] = [];
            }
            $previous = $part;
        }
        $currentLevel[$previous][] = $route;

    }

    /**
     * Dump all routes
     * @return array
     */
    public function debugGetDefinedRoutes()
    {
        return $this->map;
    }

    /**
     * Finds the route that is being requested
     * @return Route
     * @throws \Exception
     */
    private function findRoute()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REDIRECT_URL'];
        $requestUrlParts = explode("/", $requestUrl);
        $requestUrlPartCount = count($requestUrlParts);

        $this->requestMethod = $requestMethod;
        $this->requestUrl = $requestUrl;
        $this->requestUrlParts = $requestUrlParts;

        //Check if any route has the requested method
        if (!array_key_exists($requestMethod, $this->map))
        {
            throw new \Exception('Route not found');
        }

        $currentSearchLevel = $this->map[$requestMethod];
        $currentLinearSearchLevel = $requestMethod;

        // Search every request URL part
        foreach ($requestUrlParts as $i => $part)
        {
            //echo ("<br><br>Searching for '{$part}' <br>");

            // Skip if empty
            if (empty($part))
            {
                continue;
            }

            //echo "Checking in " . $currentLinearSearchLevel . "<br>";
            // Specific part of the route was found in map
            if (array_key_exists($part, $currentSearchLevel))
            {
                // Use that part of the map further on
                $currentSearchLevel = $currentSearchLevel[$part];
                $currentLinearSearchLevel .= "->$part";
                //echo ("Counts needed: {$requestUrlPartCount} Counts searched " .($i+1). " <br>");

                // Check if we are at the end of the request parts
                if ($requestUrlPartCount == $i + 1)
                {
                    // If we are at the end, we check if there is a non-array element.
                    // If we find one, that means that there is a route that ends here
                    foreach ($currentSearchLevel as $k => $levelRouteMap)
                    {
                        if (!is_array($levelRouteMap))
                        {
                            return $levelRouteMap;
                        }
                        throw new \Exception('Route not found -1');

                    }
                }
                //echo ("Going deeper " . $currentLinearSearchLevel." <br>");

            } // If we don't have a specific part, it could be a placeholder
            else if (array_key_exists(self::$placeholderValue, $currentSearchLevel))
            {
                // Use that part of the map further on
                $currentSearchLevel = $currentSearchLevel[self::$placeholderValue];
                $currentLinearSearchLevel .= "->" . self::$placeholderValue;
                //echo ("Counts needed: {$requestUrlPartCount} Counts searched " .($i+1). " <br>");

                // Check if we are at the end of the request parts
                if ($requestUrlPartCount == $i + 1)
                {
                    // If we are at the end, we check if there is a non-array element.
                    // If we find one, that means that there is a route that ends here
                    foreach ($currentSearchLevel as $k => $levelRouteMap)
                    {
                        if (!is_array($levelRouteMap))
                        {
                            return $levelRouteMap;
                        }
                        throw new \Exception('Route not found -1');

                    }
                }
                //echo ("Going deeper " . $currentLinearSearchLevel . " <br>");
            } else
            {
                throw new \Exception('Route not found 0');
            }
        }

        throw new \Exception('Route not found');
    }

    /**
     * Call the route
     * @param Route $route
     */
    private function resolveRoute(Route $route)
    {
        $route->call($this->requestUrlParts);
    }
}
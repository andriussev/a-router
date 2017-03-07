<?php namespace Andriussev\ARouter;

class Router {

    private $routeValidator;

    private $map = [];

    private $requestMethod = null;
    private $requestUrl = null;

    public function __construct() {
        $this->routeValidator = new RouteValidator();
    }

    /**
     *  Starts the router. This resolves the request.
     * @param null $forceMethod
     * @param null $forceUri
     */
    public function start($forceMethod = null, $forceUri = null) {
        $this->beforeStart();
        $matchedRoute = $this->findRoute($forceMethod, $forceUri);
        $this->resolveMatchedRoute($matchedRoute);
        $this->afterStart();
    }

    /**
     * Actions to perform before starting
     */
    private function beforeStart() {
        return;
    }

    /**
     * Actions to perform after starting
     */
    private function afterStart() {
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
    public function addRoute($method, $endpoint, $action) {
        $method = strtoupper($method);
        // Validate if method allowed
        if (!$this->routeValidator->isValidMethod($method)) {
            throw new \Exception('Method not allowed');
        }
        // Validate if endpoint is valid
        if (!$this->routeValidator->isValidEndpoint($endpoint)) {
            throw new \Exception('Endpoint is not valid');
        }
        // Create a new route object
        $route = new Route();
        $route->setMethod($method);
        $route->setEndpoint($endpoint);
        $route->setAction($action);
        // Add newly created route to array
        $this->addRouteToMap($route);

        // Return route for further chaining
        return $route;
    }

    private function addRouteToMap(Route $route) {
        if (!array_key_exists($route->getMethod(), $this->map)) {
            $this->map[$route->getMethod()] = [];
        }

        $this->map[$route->getMethod()][] = $route;
    }

    /**
     * @param bool $simplify If true, only returns the route url, without the object
     * @return array
     */
    public function getMap($simplify = false) {
        if($simplify) {
            $simplified = [];
            /** @var Route $route */
            foreach($this->map as $method => $methodGroupElements) {

                $simplified[$method] = [];
                foreach($methodGroupElements as $route) {
                    $simplified[$method][] = $route->getEndpointNormalized();
                }
            }
            return $simplified;
        }
        return $this->map;
    }

    /**
     * @param $requestMethod
     * @param $requestUrl
     * @return MatchedRoute
     * @throws \Exception
     */
    private function findRoute($requestMethod, $requestUrl) {
        if ($requestMethod === null) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }
        if ($requestUrl === null) {
            $requestUrl = $_SERVER['REDIRECT_URL'];
        }

        $requestUrl = rtrim($requestUrl,'/');

        if (!array_key_exists($requestMethod, $this->map)) {
            throw new \Exception('Route not found');
        }

        /** @var Route $routeObj */
        foreach ($this->map[$requestMethod] as $routeObj) {

            $matches = null;
            if($requestUrl == str_replace('\/','/',$routeObj->getEndpointNormalized())) {
                return $this->createMatchedRoute($requestUrl, $routeObj, []);
            }

            preg_match('/' . $routeObj->getEndpointNormalized() . '$/i', $requestUrl, $matches);
            if (count($matches) > 1) {
                array_shift($matches);

                return $this->createMatchedRoute($requestUrl, $routeObj, $matches);
            }
        }

        throw new \Exception('Route not found');
    }

    /**
     * @param $requestUrl
     * @param $routeObj
     * @param $matches
     * @return MatchedRoute
     */
    private function createMatchedRoute($requestUrl, $routeObj, $matches) {
        $matchedRoute = new MatchedRoute();
        $matchedRoute->setRoute($routeObj);
        $matchedRoute->setRequestedUrl($requestUrl);
        $matchedRoute->setMatchedPlaceholdersValues($matches);

        return $matchedRoute;
    }

    /**
     * Resolves the route
     * @param MatchedRoute $matchedRoute
     */
    private function resolveMatchedRoute(MatchedRoute $matchedRoute) {
        $matchedRoute->resolve();
    }
}
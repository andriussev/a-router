<?php namespace Andriussev\ARouter;

use Andriussev\ARouter\Exception\EndpointInvalidException;
use Andriussev\ARouter\Exception\MethodInvalidException;
use Andriussev\ARouter\Exception\NotFoundHandlerInvalidException;
use Andriussev\ARouter\Exception\RouteNotFoundException;

class Router {

    private $normalizeNamedBeforeFindingRoute = false;
    private $isStarted = false;

    private $routeValidator;

    private $routeList = [];
    private $map = [];
    private $mapByName = [];

    private $notFoundHandler;

    private $requestMethod = null;
    private $requestUrl = null;

    public function __construct() {
        $this->routeValidator = new Validator();
        Helper::setRouter($this);
    }

    /**
     *  Starts the router. This resolves the request.
     * @param null $forceMethod
     * @param null $forceUri
     */
    public function start($forceMethod = null, $forceUri = null) {
        $this->beforeStart();
        $this->mapRoutes();
        $this->isStarted = true;
        if ($this->normalizeNamedBeforeFindingRoute) {
            $this->normalizeNamedRoutes();
        }
        $matchedRoute = $this->findRoute($forceMethod, $forceUri);
        if(!$matchedRoute) {
            return;
        }
        Helper::setMatchedRoute($matchedRoute);
        $this->resolveMatchedRoute($matchedRoute);
        $this->afterStart();
    }

    public function isStarted() {
        return $this->isStarted();
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
     * @throws EndpointInvalidException
     * @throws MethodInvalidException
     */
    public function addRoute($method, $endpoint, $action) {
        $method = strtoupper($method);
        // Validate if method allowed
        if (!$this->routeValidator->isValidMethod($method)) {
            throw new MethodInvalidException('Method \'' . $method . '\' is not valid');
        }
        // Validate if endpoint is valid
        if (!$this->routeValidator->isValidEndpoint($endpoint)) {
            throw new EndpointInvalidException('Endpoint \'' . $endpoint . '\' is not valid');
        }
        // Create a new route object
        $route = new Route();
        $route->setMethod($method);
        $route->setEndpoint($endpoint);
        $route->setAction($action);
        // Add newly created route to array
        $this->routeList[] = $route;
        //$this->addRouteToMap($route);

        // Return route for further chaining
        return $route;
    }

    public function addGroup($endpoint) {
        $group = new Group();
        $group->setEndpoint($endpoint);

        return $group;
    }

    public function normalizeNamedBeforeFindingRoute() {
        $this->normalizeNamedBeforeFindingRoute = true;
    }

    public function setNotFoundHandler($action) {
        if(!is_callable($action)) {
            throw new NotFoundHandlerInvalidException('Provided not-found handler is not a callable function');
        }
        $this->notFoundHandler = $action;
    }

    private function mapRoutes() {
        $this->clearRoutesMap();
        foreach ($this->routeList as $route) {
            $this->addRouteToMap($route);
        }
    }

    private function clearRoutesMap() {
        $this->map = [];
        $this->mapByName = [];
    }

    private function addRouteToMap(Route $route) {
        if (!array_key_exists($route->getMethod(), $this->map)) {
            $this->map[$route->getMethod()] = [];
        }

        $this->map[$route->getMethod()][] = $route;

        if ($route->getName() !== null) {
            $this->mapByName[$route->getName()] = $route;
        }
    }

    /**
     * @param bool $simplify If true, only returns the route url, without the object
     * @return array
     */
    public function getMap($simplify = false) {
        $this->clearRoutesMap();
        $this->mapRoutes();
        if ($simplify) {
            $simplified = [];
            /** @var Route $route */
            foreach ($this->map as $method => $methodGroupElements) {

                $simplified[$method] = [];
                foreach ($methodGroupElements as $route) {
                    $simplified[$method][] = $route->getEndpointNormalized();
                }
            }

            return $simplified;
        }

        return $this->map;
    }

    /**
     * Returns all named routes
     * @return array
     */
    public function getMapByName() {
        return $this->mapByName;
    }

    private function normalizeNamedRoutes() {
        foreach ($this->mapByName as $route) {
            $route->getEndpointNormalized();
        }
    }

    /**
     * @param $requestMethod
     * @param $requestUrl
     * @return MatchedRoute
     * @throws RouteNotFoundException
     */
    private function findRoute($requestMethod, $requestUrl) {
        if ($requestMethod === null) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }
        if ($requestUrl === null) {
            $requestUrl = strtok($_SERVER["REQUEST_URI"], '?');
        }

        $requestUrl = rtrim($requestUrl, '/');

        if (!array_key_exists($requestMethod, $this->map)) {
            $this->routeNotFound($requestMethod, $requestUrl);
        }

        /** @var Route $routeObj */
        foreach ($this->map[$requestMethod] as $routeObj) {

            $matches = null;
            if ($requestUrl == str_replace('\/', '/', $routeObj->getEndpointNormalized())) {
                return $this->createMatchedRoute($requestUrl, $routeObj, []);
            }

            preg_match('/' . $routeObj->getEndpointNormalized() . '$/i', $requestUrl, $matches);
            if (count($matches) > 1) {
                array_shift($matches);

                return $this->createMatchedRoute($requestUrl, $routeObj, $matches);
            }
        }

        $this->routeNotFound($requestMethod, $requestUrl);
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

    /**
     * @param $requestMethod
     * @param $requestUrl
     * @return mixed
     * @throws RouteNotFoundException
     */
    private function routeNotFound($requestMethod, $requestUrl) {
        if($this->notFoundHandler) {
            return call_user_func_array($this->notFoundHandler,[$requestMethod,$requestUrl]);
        }
        throw new RouteNotFoundException('Route not found for method \'' . $requestMethod . '\' URL \'' . $requestUrl . '\'');
    }
}
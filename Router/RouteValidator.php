<?php
namespace Router;

class RouteValidator {
    
    private $allowedMethods = ['GET'];

    /**
     * Check if the HTTP method is allowed
     * @param $method string
     * @return bool
     */
    public function isValidMethod($method)
    {
        return in_array($method,$this->allowedMethods);
    }

    /**
     * Check if endpoint is valid
     * @param $endpoint
     * @return bool
     */
    public function isValidEndpoint($endpoint)
    {
        return true;
    }

    /**
     * Check if action is valid
     * @param $action
     * @return bool
     */
    public function isValidAction($action)
    {
        return !is_callable($action);
    }

}
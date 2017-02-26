<?php
namespace Router;

class RouteValidator {
    
    private $allowedMethods = ['GET'];
    
    public function isValidMethod($method)
    {
        return in_array($method,$this->allowedMethods);
    }
    
    public function isValidEndpoint($endpoint)
    {
        return true;
    }
    
    public function isValidAction($action)
    {
        return !is_callable($action);
    }

}
<?php
namespace Router;

class Route {
    
    private $method = null;
    private $endpoint = null;
    private $endpointNormalized = null;
    private $endpointPartsNormalized = [];
    private $placeHolders = [];
    private $placeHolderPositions = [];
    private $action = null;
    
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
    }
    
    public function setEndpoint($endpoint)
    {
        $this->normalize($endpoint);
        // $this->placeholders = $this->findPlaceholders($endpoint);
        // $this->endpointNormalized = $this->normalizeEndpoint($endpoint);
        $this->endpoint = $endpoint;
    }
    
    public function getNormalizedEndpoint()
    {
        return $this->endpointNormalized;
    }
    
    public function getNormalizedEndpointParts()
    {
        return $this->endpointPartsNormalized;
    }
    
    public function getHierarchy()
    {
        $hierarchy = [];
        $parts = array_reverse(explode("/",$this->endpointNormalized));
        $prev = null;
        foreach($parts as $k => $part) {
            
            if($k == 0) {
                $hierarchy[$part] = $this->endpointNormalized;
                continue;
            }
            
            $hierarchy = [$part=>$hierarchy];
            
        }
        
        return $hierarchy;
    }
    
    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function call($requestParts)
    {
        $placeHolderValues = $this->parsePlaceholderValues($requestParts);
        
        return call_user_func_array($this->action,$placeHolderValues);
        // return ($this->action)($requestParts);
    }
    
    private function parsePlaceholderValues($requestParts)
    {
        $values = [];
        foreach($this->placeHolders as $placeholder) {
            $values[] = $requestParts[$this->placeHolderPositions[$placeholder]];
        }
        
        return $values;
    }
    
    /*
     * Extracts all placeholders.
     * Normalizes endpoint by replacing placeholders with static value
     */
    private function normalize($endpoint)
    {
        // Define all placeholders in route
        $placeHolders = [];
        
        // Remember at which position the placeholders are stored in the endpoint
        $placeHolderPositions = [];
        
        // Normalized endpoint - all placeholders replaced
        $endpointPartsNormalized = [];
        
        $endpointParts = explode("/",$endpoint);
        
        foreach($endpointParts as $k => $part) {
            if(empty($part)) {
                continue;
            }
            if($part[0] == ":") {
                $variable = substr($part,1,strlen($part)-1);
                $placeHolders[] = $variable;
                $endpointPartsNormalized[] = Router::$placeholderValue;
                $placeHolderPositions[$variable] = $k;
                continue;
            }
            
            $endpointPartsNormalized[] = $part;
        }
        
        $this->endpointPartsNormalized = $endpointPartsNormalized;
        $this->endpointNormalized = implode('/',$endpointPartsNormalized);
        $this->placeHolders = $placeHolders;
        $this->placeHolderPositions = $placeHolderPositions;
        
    }
    
}
<?php
namespace Router;

class Route {

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $endpointNormalized;

    /**
     * Endpoint parts, with placeholders replaced
     * @var array
     */
    private $endpointPartsNormalized = [];

    /**
     * Placeholders in route
     * @var array
     */
    private $placeHolders = [];

    /**
     * Placeholder positions in endpoint parts
     * @var array
     */
    private $placeHolderPositions = [];

    /**
     * Function to be called if route is matched
     * @var callable
     */
    private $action;


    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method string
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->normalize($endpoint);
        // $this->placeholders = $this->findPlaceholders($endpoint);
        // $this->endpointNormalized = $this->normalizeEndpoint($endpoint);
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getNormalizedEndpoint()
    {
        return $this->endpointNormalized;
    }

    /**
     * @return array
     */
    public function getNormalizedEndpointParts()
    {
        return $this->endpointPartsNormalized;
    }

    /**
     * @param $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Call the function for route.
     * @param $requestParts
     * @return mixed
     */
    public function call($requestParts)
    {
        $placeHolderValues = $this->parsePlaceholderValues($requestParts);
        
        return call_user_func_array($this->action,$placeHolderValues);
        // return ($this->action)($requestParts);
    }

    /**
     * Replaces placeholder variables with ones in request
     * @param $requestParts
     * @return array
     */
    private function parsePlaceholderValues($requestParts)
    {
        $values = [];
        foreach($this->placeHolders as $placeholder) {
            $values[] = $requestParts[$this->placeHolderPositions[$placeholder]];
        }
        
        return $values;
    }

    /**
     * Extracts all placeholders.
     * Normalizes endpoint by replacing placeholders with static value
     * @param $endpoint string
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
<?php namespace Andriussev\ARouter;

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
     * The endpoint, prepared to be used with regular expressions
     * @var string
     */
    private $endpointNormalized;

    /**
     * The endpoint, after adding the belonging group
     * @var string
     */
    private $endpointGenerated;

    /**
     * Function to be called if route is matched
     * @var callable
     */
    private $action;

    /**
     * All placeholders for the route
     * @var array
     */
    private $placeholders = [];
    
    /**
     * The group that this route belongs to
     * @var Group 
     */
    private $group;
    
    /**
     * Name of the route
     * @var string
     */
    private $name;


    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param $method string
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @param $endpoint
     */
    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
        //$this->generateNormalizedEndpoint();
    }
    
    public function setGroup(Group $group) {
        $this->group = $group;
        return $this;
    }
    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
    public function getName() {
        return $this->name;
    }

    /**
     * @param callable $action
     */
    public function setAction($action) {
        $this->action = $action;
    }
    

    /**
     * @return callable
     */
    public function getAction() {
        return $this->action;
    }

    private function generateNormalizedEndpoint() {
        $finalEndpoint = $this->endpoint;
        
        // If the route has a group, prepend the group url first
        if($this->group) {
            $groupEndpoint = $this->group->getEndpoint();
            $groupEndpoint = rtrim($groupEndpoint,'/');
            $finalEndpoint = $groupEndpoint . $finalEndpoint;
        }
        
        // Set the generated endpoint - group+endpoint
        $this->endpointGenerated = (strlen($finalEndpoint)!=1) ? rtrim($finalEndpoint,'/') : $finalEndpoint;
        
        $endpointParts = explode('/', $finalEndpoint);
        $endpointOut = '';
        foreach ($endpointParts as $part) {
            // Skip a possible first and last empty parts
            if (empty($part)) {
                continue;
            }

            // Check if part is a placeholder
            if ($part[0] == ':') {
                $this->placeholders[] = substr($part,1);
                $part = '([a-z0-9]{1,})';
            }

            $endpointOut .= '\/' . $part;

        }
        
        $this->endpointNormalized = $endpointOut;
    }

    /**
     * Gets the normalized
     * @return string
     */
    public function getEndpointNormalized() {
        if($this->endpointNormalized) {
            return $this->endpointNormalized;
        }
        
        $this->generateNormalizedEndpoint();
        
        return $this->endpointNormalized;
    }

    /**
     * @return array
     */
    public function getPlaceholders() {
        return $this->placeholders;
    }

    /**
     * Calls the closure action for route
     * @param $placeholderValues
     * @return mixed
     */
    public function call($placeholderValues) {
        return call_user_func_array($this->action,$placeholderValues);
    }
    
    public function generateUrl($values) {
        // Differences in arrays between placeholders and provided values
        $differences = array_diff($this->placeholders,array_keys($values));
        if(!empty($differences)) {
            throw new \Exception('Missing values to generate a URL: ' . implode(',',$differences));
        }
        
        $outputUrl = $this->endpointGenerated;
        // Replace the placeholders in the generated endpoint with the provided values
        foreach($this->placeholders as $placeholder) {
            $outputUrl = str_replace(':'.$placeholder,$values[$placeholder],$outputUrl);
        }
        
        return $outputUrl;
    }

}
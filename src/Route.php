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
     * @var string
     */
    private $endpointNormalized;

    /**
     * Function to be called if route is matched
     * @var callable
     */
    private $action;

    /**
     * All placeholders for the route
     * @var array
     */
    private $placeHolders = [];
    
    private $group;


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

    private function generateNormalizedEndpoint() {
        $finalEndpoint = $this->endpoint;
        
        // If the route has a group, prepend the group url first
        if($this->group) {
            $groupEndpoint = $this->group->getEndpoint();
            $groupEndpoint = rtrim($groupEndpoint,'/');
            $finalEndpoint = $groupEndpoint . $finalEndpoint;
        }
        
        $endpointParts = explode('/', $finalEndpoint);
        $endpointOut = '';
        foreach ($endpointParts as $part) {
            // Skip a possible first and last empty parts
            if (empty($part)) {
                continue;
            }

            // Check if part is a placeholder
            if ($part[0] == ':') {
                $this->placeHolders[] = substr($part,1);
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
     * @return callable
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param callable $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getPlaceHolders() {
        return $this->placeHolders;
    }

    /**
     * Calls the closure action for route
     * @param $placeholderValues
     * @return mixed
     */
    public function call($placeholderValues) {
        return call_user_func_array($this->action,$placeholderValues);
    }

    public function setGroup(Group $group) {
        $this->group = $group;
        return $this;
    }

}
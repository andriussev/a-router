<?php namespace Andriussev\ARouter;

class MatchedRoute {

    /**
     * The route that was matched
     * @var Route
     */
    private $route;

    /**
     * Url that was requested on the server
     * @var string
     */
    private $requestedUrl;

    /**
     * A list of placeholders that were matched
     * @var array
     */
    private $matchedPlaceholdersValues;

    /**
     * List of placeholder values with placeholders as keys
     * @var array
     */
    private $matchedPlaceholders;

    /**
     * @param Route $route
     */
    public function setRoute($route) {
        $this->route = $route;
    }

    /**
     * @param string $requestedUrl
     */
    public function setRequestedUrl($requestedUrl) {
        $this->requestedUrl = $requestedUrl;
    }

    /**
     * @param array $matchedPlaceholdersValues
     */
    public function setMatchedPlaceholdersValues($matchedPlaceholdersValues) {
        $this->matchedPlaceholdersValues = $matchedPlaceholdersValues;
    }

    /**
     * Sets placeholder keys with values.
     * Calls the route callback function to resolve the route.
     */
    public function resolve() {
        $this->matchedPlaceholders = array_combine($this->route->getPlaceHolders(),$this->matchedPlaceholdersValues);
        $this->route->call($this->matchedPlaceholders);
    }
    
    /**
     * Returns an array of matched route keys and values
     * @return array
     */
    public function getMatchedPlaceholders() {
        return $this->matchedPlaceholders;
    }
        
    /**
     * Returns an array of matched route keys and values
     * @return array
     */
    public function values() {
        return $this->getMatchedPlaceholders();
    }


}
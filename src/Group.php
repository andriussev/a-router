<?php namespace Andriussev\ARouter;

class Group {
    
    private $endpoint;
    
    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
    }
    
    public function getEndpoint() {
        return $this->endpoint;
    }
    
}
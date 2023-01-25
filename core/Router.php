<?php

class Router {
    private $request;
    public $urlParams;
    private $supportedHttpMethods = ["GET", "POST", "PUT", "DELETE"];

    function __construct(RequestInterface $request) {
        $this->request = $request;
        $this->urlParams = [];
    }

    function __call($name, $args) {
        list($route, $method) = $args;

        if(!in_array(strtoupper($name), $this->supportedHttpMethods)) {
            $this->invalidMethodHandler();
        }

        $this->{
            strtolower($name)
        }[$this->formatRoute($route)] = $method;
    }

    private function formatRoute($route) {
        $result = rtrim($route, '/');

        if($result === '') {
            return '/';
        }

        return $result;
    }

    private function invalidMethodHandler() {
        header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }

    private function defaultRequestHandler() {
        header("{$this->request->serverProtocol} 404 Not Found");
    }

    function resolve() {
        $methodDictionary = $this->{
            strtolower($this->request->requestMethod)
        };
        
        $formattedRoute = $this->formatRoute($this->request->requestUri);
        $formattedRouteSchema = $this->formatRoute($this->request->requestUriSchema);
        $method = null;

        if(in_array($formattedRoute, $methodDictionary)) {
            $method = $methodDictionary[$formattedRoute];
        } else {
            $method = $methodDictionary[$formattedRouteSchema];
        }

        for($i = 0; $i < count($this->request->urlParams); $i++) {
            $this->urlParams[
                substr(
                    $this->request->urlParams[$i]['label'],
                    1,
                    strlen($this->request->urlParams[$i]['label'])
                )
            ] = $this->request->urlParams[$i]['value'];
        }

        $this->request->urlParams = $this->urlParams;

        if(is_null($method)) {
            $this->defaultRequestHandler();

            return;
        }

        echo call_user_func_array($method, array($this->request));
    }

    function __destruct() {
        $this->resolve();
    }
}

?>
<?php

include_once('RequestInterface.php');

class Request implements RequestInterface {
    public $urlParams;
    private $uriParams;

    public function __construct() {
        $this->urlParams = [];
        $this->uriParams = [];
        $this->bootstrapSelf();
    }

    private function setUriParams($uriParams) {
        foreach($uriParams as $uri) {
            $uriParts = explode('=', $uri);
            $this->uriParams[$uriParts[0]] = $uriParts[1];
        }
    }

    private function extractUriParams($trimmedValue) {
        $cleanTrimmedValue = $trimmedValue;

        if(strpos($trimmedValue, '?') !== false) {
            $urlWithUriParams = explode('?', $trimmedValue);

            if(count($urlWithUriParams) > 1) {
                $cleanTrimmedValue = trim($urlWithUriParams[0]);
                $uriParamsString = $urlWithUriParams[1];

                if(strpos($uriParamsString, '&amp;') !== false) {
                    $this->setUriParams(explode('&amp;', $uriParamsString));
                }
            }
        }

        return $cleanTrimmedValue;
    }

    private function setJSONResponseHeader($url) {
        if(strpos($url, '/' . BASE_URI . '/') != false) {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
        }
    }

    private function bootstrapSelf() {
        foreach($_SERVER as $key => $value) {
            if($key == 'REQUEST_URI') {
                $route = htmlspecialchars($value);
                $this->setJSONResponseHeader($route);
                $urlParts = explode('/', $route);
                $urlArray = [];
                $value = '/';
                $valueSchema = '/';

                foreach($urlParts as $url) {
                    $trimmedValue = $this->extractUriParams(trim($url));
                    
                    if(strlen($trimmedValue) != 0 && $trimmedValue != BASE_URI) {
                        array_push($urlArray, $trimmedValue);
                    }
                }

                if(count($urlArray) >= 2) {
                    for($i = 0; $i < count($urlArray); $i += 2) {
                        $label = ':' . substr($urlArray[$i], 0, strlen($urlArray[$i]));
                        
                        if(array_key_exists($i + 1, $urlArray)) {
                            array_push($this->urlParams, [
                                'label' => $label,
                                'value' => $urlArray[$i + 1]
                            ]);
                        }
                    }
                }

                $value .= implode('/', $urlArray);
                $tick = 0;

                for($i = 0; $i < count($urlArray); $i++) {
                    if($i % 2 == 0) {
                        if($i > 0) {
                            $valueSchema .= '/';
                        }

                        $valueSchema .= $urlArray[$i];
                    } else {
                        if($i > 0) {
                            $valueSchema .= '/';
                        }

                        $valueSchema .= $this->urlParams[$tick]['label'];
                        $tick++;
                    }
                }
                
                $this->requestUriSchema = $valueSchema;
            }
                
            $this->{
                $this->toCamelCase($key)
            } = $value;
        }
    }

    private function toCamelCase($string) {
        $result = strtolower($string);
        preg_match_all('/_[a-z]/', $result, $matches);

        foreach($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }
        
        return $result;
    }

    public function getUrlQuery() {
        if($this->requestMethod == 'POST' || $this->requestMethod == 'PUT') {
            return null;
        } else if($this->requestMethod == 'GET' || $this->requestMethod == 'DELETE') {
            return $this->uriParams;
        }
    }

    public function getBody() {
        if($this->requestMethod == 'GET' || $this->requestMethod == 'DELETE') {
            return null;
        } else if($this->requestMethod == 'POST' || $this->requestMethod == 'PUT') {
            $post = [];
            $body = [];
            
            if (isset($_POST) && !empty($_POST)) {
                if(isset($_POST['data']) && !empty($_POST['data'])) {
                    $post = json_decode($_POST['data'], true);
                }
            } else if(file_get_contents("php://input")) {
                $post = json_decode(file_get_contents("php://input"), true);
            }

            if(isset($post) && !empty($post)) {
                foreach($post as $key => $value) {
                    $body[$key] = $value;
                }
            }

            return $body;
        }
    }

    public function getFiles() {
        if (isset($_FILES) && !empty($_FILES)) {
            return $_FILES;
        }

        return null;
    }
}

?>

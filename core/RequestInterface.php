<?php

interface RequestInterface {
    public function getUrlQueryParams();

    public function getBody();
    
    public function getFiles();
}

?>
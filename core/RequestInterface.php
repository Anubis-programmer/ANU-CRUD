<?php

interface RequestInterface {
    public function getUrlQuery();

    public function getBody();
    
    public function getFiles();
}

?>

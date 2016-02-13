<?php

class WebSPA extends BaseAPIController {
    function get() {
        if ($this->checkAuth()) {
            include_once(__DIR__ . '/../Views/index.html');
        } else {
            StatusReturn::WEB401('401 Not Authorized!');
        }
    }
}
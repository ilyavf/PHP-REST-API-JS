<?php

class WebSPA extends BaseAPIController {
    function get($key = null) {
        if ($this->checkAuth()) {
            if (!is_null($key)) {
                // todo: this is a confirm key for signup. check extra key and set email confirmed
            }
            include_once(__DIR__ . '/../Views/index.html');
        }
    }
}
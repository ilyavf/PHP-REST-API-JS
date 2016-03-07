<?php

class HelloWorld extends BaseAPIController {
    function get_xhr() {
        if ($this->checkAuth()) {
            echo json_encode(StatusReturn::S200(Array('data' => 'Hello World')));
        }
    }
}
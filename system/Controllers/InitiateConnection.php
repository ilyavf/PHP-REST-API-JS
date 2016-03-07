<?php

class InitiateConnection extends BaseAPIController {
    function post_xhr() {
        if ($this->checkAuth()) {
            echo json_encode(StatusReturn::S200());
        }
    }
}
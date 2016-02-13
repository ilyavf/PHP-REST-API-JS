<?php

class InitiateConnection extends BaseAPIController {
    function get_xhr() {
        if ($this->checkAuth()) {
            echo json_encode(StatusReturn::S200());
        }
    }
}
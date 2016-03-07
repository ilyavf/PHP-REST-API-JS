<?php

class SignUp extends BaseAPIController {
    function post_xhr() {
        if ($this->checkAuth()) {
            //todo: add email validation
            if (strlen($_POST['user']) >= 8 && preg_match('/^[a-zA-Z0-9_\-]+$/', $_POST['user'])
                && $_POST['email'] != '' && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
                && $_POST['question'] != ''
                && strlen($_POST['answer']) >= 6
                && $_POST['pass'] == $_POST['retype']
                && ($_POST['factor'] == '' || strlen($_POST['answer']) >= 6)) {

                $newUser = new AuthUser();
                if ($newUser->createUser(strtolower($_POST['user']), strtolower($_POST['email']), strtolower($_POST['pass']), $_POST['question'], strtolower($_POST['answer']), strtolower($_POST['factor']))) {
                    if (isset($_POST['lang']) && $_POST['lang'] != '' && strlen($_POST['lang']) == 2 && ctype_alpha($_POST['lang'])) $newUser->changeLanguage($_POST['lang']);
                    echo json_encode(StatusReturn::S200());
                } else {
                    echo json_encode(StatusReturn::E400('Unknown Error'));
                }
            } else {
                echo json_encode(StatusReturn::E400('Unknown Error'));
            }
        }
    }
}

class SignUpUserName extends BaseAPIController {
    function get_xhr($authUser) {
        if ($this->checkAuth()) {
            if (!AuthUserData::userExist(strtolower($authUser))) echo json_encode(StatusReturn::S200());
            else {
                echo json_encode(StatusReturn::E400('Username Exists Already!'));
            }
        }
    }
}

class SignUpEmail extends BaseAPIController {
    function get_xhr($email) {
        if ($this->checkAuth()) {
            if (!AuthUserData::emailExist(strtolower($email))) echo json_encode(StatusReturn::S200());
            else {
                echo json_encode(StatusReturn::E400('Email Already Being Used!'));
            }
        }
    }
}
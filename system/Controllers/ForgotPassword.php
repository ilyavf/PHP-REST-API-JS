<?php

class ForgotPassword extends BaseAPIController {
    function post_xhr() {
        if ($this->checkAuth()) {
            $usernameOrEmail = strtolower($_POST['usernameOrEmail']);
            if ((strlen($usernameOrEmail) >= 8 && preg_match('/^[a-zA-Z0-9_\-]+$/', $usernameOrEmail)) || filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                $secondFactor = strtolower($_POST['secondFactor']);
                if (ctype_alnum($secondFactor) || $secondFactor == '') {
                    $answer = strtolower($_POST['answer']);
                    if (strlen($answer) >= 6 || $answer == '') {
                        $newPassword = $_POST['passwordForgot'];
                        $newRetypedPassword = $_POST['passwordRetypedForgot'];
                        if ($newPassword == $newRetypedPassword) {
                            $userForgot = new AuthUser();
                            $responseArr = $userForgot->forgotPassword($usernameOrEmail, $secondFactor, $answer, $newPassword);

                            if ($responseArr['continue'] == true) {
                                echo json_encode(StatusReturn::S200($responseArr));
                            } else {
                                echo json_encode(StatusReturn::E400('Unknown Error 5'));
                            }
                        } else {
                            echo json_encode(StatusReturn::E400('Unknown Error 4'));
                        }
                    } else {
                        echo json_encode(StatusReturn::E400('Unknown Error'));
                    }
                } else {
                    echo json_encode(StatusReturn::E400('Unknown Error'));
                }
            } else {
                echo json_encode(StatusReturn::E400('Unknown Error'));
            }
        }
    }
}
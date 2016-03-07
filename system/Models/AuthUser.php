<?php

class AuthUser {
    private $userData;
    private $userRoles;
    private $userSession;
    private $activeSessionSecret;

    public function __construct() {}

    public function createUser($authUser, $authEmail, $authPass, $authQuestion, $authAnswer, $extraKey) {
        $emailExists = AuthUserData::emailExist($authEmail);
        $userExists = AuthUserData::userExist($authUser);

        if ($userExists && $emailExists && $extraKey != '') {
            $this->loadUser($authUser);

            if ($this->checkKey($extraKey, 'SignUp')) {
                AuthUserData::clearExtraKey($this->userData['userID']);
                return false;
            }
            if ($this->userData['password'] != $authPass || $this->userData['securityQuestion'] != $authQuestion || $this->userData['securityAnswer'] != $authAnswer) {
                AuthUserData::clearExtraKey($this->userData['userID']);
                return false;
            }

            $this->createAndUpdatePassword($authPass);
            AuthUserData::clearExtraKey($this->userData['userID']);
            AuthUserData::confirmEmail($this->userData['userID']);

            return true;
        } else if (!$userExists && !$emailExists && $extraKey == '') {
            $newExtraKey = $this->createPin(_PIN_SIGN_UP_PLUS_CHARS_);
            $salt = bin2hex(mcrypt_create_iv(_PASSWORD_SALT_IV_SIZE_, MCRYPT_DEV_URANDOM));
            $userID = AuthUserData::addNewUser($authUser, $authEmail, $authPass, $salt, $authQuestion, $authAnswer);
            $this->loadUserForced($authUser);
            AuthUserData::addUserRole($userID, 'Admin');
            AuthUserData::updateExtraKey($userID, $newExtraKey, 'SignUp');
            header('Auth-Second-Factor: true');
            $this->sendEmailNotification('SignUp', Array(Array('{{PIN}}'), Array(strtoupper($newExtraKey))));

            return true;
        }
        return false;
    }

    public function forgotPassword($userOrEmail, $secondFactor, $answer, $newPassword) {
        if (AuthUserData::emailExist($userOrEmail)) {
            $userAuth = AuthUserData::getUserByEmail($userOrEmail);
            $this->loadUserForced($userAuth);
        } else if (AuthUserData::userExist($userOrEmail)) {
            $userAuth = $userOrEmail;
            $this->loadUserForced($userAuth);
        } else {
            return Array("continue" => false);
        }

        if ($secondFactor != '') {
            if ($this->checkKey($_POST['secondFactor'], 'forgotPassword')) {
                if ($answer != '') {
                    if ($answer == $this->userData['securityAnswer']) {
                        if ($newPassword != '' && $newPassword != hash('sha512', '')) {
                            $this->createAndUpdatePassword($newPassword);
                            AuthUserData::clearExtraKey($this->userData['userID']);
                            return Array("continue" => true, "flowDone" => true);
                        } else {
                            return Array("continue" => true, "askForNewPassword" => true);
                        }
                    }
                } else {
                    return Array("continue" => true, "question" => $this->userData['securityQuestion']);
                }
            } else {
                AuthUserData::clearExtraKey($this->userData['userID']);
            }
        } else {
            $newExtraKey = $this->createPin(_PIN_FORGOT_PASSWORD_PLUS_CHARS_);
            AuthUserData::updateExtraKey($this->userData['userID'], $newExtraKey, 'forgotPassword');
            $this->sendNotification('ForgotPassword', Array(Array('{{PIN}}'), Array($newExtraKey)));
            return Array("continue" => true, "secondFactor" => true);
        }
        return Array("continue" => false);
    }

    public function changeLanguage($lang) {
        AuthUserData::updateLanguage($this->userData['userID'], $lang);
    }

    public function createAndUpdatePassword($newPassword) {
        $this->userData['salt'] = bin2hex(mcrypt_create_iv(_PASSWORD_SALT_IV_SIZE_, MCRYPT_DEV_URANDOM));
        $this->userData['password'] = hash_pbkdf2('sha512', $newPassword, $this->userData['salt'], 1000);

        AuthUserData::updatePasswordAndSalt($this->userData['userID'], $this->userData['password'], $this->userData['salt']);
    }

    public function checkKey($key, $type) {
        return ($key == $this->userData['extraKey'] && $this->userData['extraKeyType'] == $type && ($this->userData['extraKeyCreated'] + _SECOND_FACTOR_EXPIRE_SECONDS_) > time());
    }

    public function loadUser($authUser, $initialize = false) {
        if (AuthUserData::userExist($authUser)) {
            $this->loadUserForced($authUser);

            if (AuthUserData::userExistConfirmed($authUser)) {
                if ($initialize) {
                    return true;
                }
                return $this->findCurrentSession();
            }
        }

        return false;
    }

    public function loadUserForced($authUser) {
        $this->userData = AuthUserData::getUserData($authUser);
        $this->getUserRoles();
        $this->getUserSessions();
    }

    public static function createPin($baseLen = 0) {
        $characters = str_shuffle(_CHARS_FOR_SECOND_FACTOR_KEYS_);
        $charLen = strlen($characters) - 1;
        $len = mt_rand($baseLen+_PIN_LOWEST_NUMBER_OF_CHARS_, $baseLen+_PIN_HIGH_RANGE_NUMBER_OF_CHARS_);

        $string = '';
        for ($i = 0; $i < $len; $i++) $string .= $characters[mt_rand(0, $charLen)];
        return $string;
    }

    public function getChallengeKey() {
        if ($this->userData['extraKeyType'] == 'challenge') {
            // resets keys so this challenge cannot be retested
            AuthUserData::clearExtraKey($this->userData['userID']);
            return $this->userData['extraKey'];
        }
        return null;
    }

    public function getUserData() {
        return $this->userData;
    }

    public function getUserPassword() {
        return $this->userData['password'];
    }

    public function getSalt() {
        return $this->userData['salt'];
    }

    public function getUserSecret() {
        return $this->activeSessionSecret;
    }

    public function getUserRoles() {
        if (is_null($this->userRoles)) {
            $roles = AuthUserData::getUserRoles($this->userData['userID']);
            foreach ($roles AS $value) {
                $this->userRoles[] = $value['userRole'];
            }
        }
        return $this->userRoles;
    }

    public function getUserSessions() {
        if (is_null($this->userSession)) {
            AuthUserData::delExpiredSessions($this->userData['userID']);
            $this->userSession = AuthUserData::getUserSessions($this->userData['userID']);
        }
        return $this->userSession;
    }

    public function findCurrentSession() {
        $hashData = $this->makeSessionUserInfoHash();
        forEach ($this->userSession AS $value) {
            if ($value['sessionIP'] == $_SERVER['REMOTE_ADDR']
                        && $value['sessionUserAgent'] == $_SERVER['HTTP_USER_AGENT']
                        && $value['sessionUserInfoHash'] == $hashData) {
                $this->activeSessionSecret = $value['sessionSecret'];
                AuthUserData::updateSessionActivity($value['sessionID']);
                return true;
            }
        }
        return false;
    }

    public function createNewSession($createdSecret) {
        $this->activeSessionSecret = $createdSecret;
        $hashData = $this->makeSessionUserInfoHash();
        return AuthUserData::addNewSession($this->userData['userID'], $createdSecret, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $hashData);
    }

    public function sendEmailNotification($type, $extraVariables = null) {
        $emailTemplate = new EmailTemplates(Array($this->userData['email']), $type, $this->userData['baseLang']);
        $emailTemplate->addVariables($extraVariables);
        if (!$emailTemplate->send()) {
             // todo: add some sort of error checking/recording.
        }
    }

    public function sendSMSNotification($type, $extraVariables = null) {
        // todo: add stuff for SMS messaging
    }

    public function sendAllNotification($type, $extraVariables = null) {
        $this->sendEmailNotification($type, $extraVariables);
        $this->sendSMSNotification($type, $extraVariables);
    }

    public function sendNotification($type, $extraVariables = null) {
        if ($this->userData['twoFactorType'] == TwoFactor::Email) {
            $this->sendEmailNotification($type, $extraVariables);
        } elseif ($this->userData['twoFactorType'] == TwoFactor::SMS) {
            $this->sendSMSNotification($type, $extraVariables);
        }
    }

    public function askClientChallenge() {
        $challengePin = $this->createPin(30);
        AuthUserData::updateExtraKey($this->userData['userID'], $challengePin, 'challenge');
        header('Auth-Challenge: ' . $challengePin);
        header('Auth-Salt: ' . $this->userData['salt']);
    }

    public function initiateConnection() {
        $createdSecret = $this->createNewSecret();

        if ($this->userData['twoFactorType'] != TwoFactor::None) {
            $secondFactor = $this->createPin();
            header('Auth-Second-Factor: true');
            $createdSecret = base64_encode(hash_hmac('sha512', $createdSecret, $secondFactor, true));
            $this->sendNotification('Login2f', Array(Array('{{PIN}}'), Array($secondFactor)));
        }

        $this->createNewSession($createdSecret);
        return true;
    }

    public function createNewSecret() {
        AuthUserData::clearExtraKey($this->userData['userID']);
        $createdSecret = base64_encode(hash('sha512', sha1(microtime(true).mt_rand(10000,90000))));
        header('Auth-Secret: ' . $createdSecret);
        return $createdSecret;
    }

    public function notifyOnFailedLogin() {
        if ($this->userData['failedLoginCount'] >= _LOGIN_FAILED_COUNT_BEFORE_NOTIFICATION_) {
            AuthUserData::resetFailedLogin($this->userData['userID']);
            if ($this->userData['failedLoginTime'] + _LOGIN_FAILED_TIME_BEFORE_RESET_COUNT_SECONDS_ > time()) {
                $this->sendAllNotification('LoginFailed');
            }
            return true;
        }
        return false;
    }

    public function addFailedLogin() {
        if (!$this->notifyOnFailedLogin()) {
            if ((is_null($this->userData['failedLoginCount']) && is_null($this->userData['failedLoginTime'])) || $this->userData['failedLoginTime'] + _LOGIN_FAILED_TIME_BEFORE_RESET_COUNT_SECONDS_ < time()) {
                AuthUserData::updateFailedLogin($this->userData['userID'], 1, time());
            } else {
                AuthUserData::updateFailedLogin($this->userData['userID'], ($this->userData['failedLoginCount'] + 1), $this->userData['failedLoginTime']);
            }
        }
    }

    public function makeSuccessfulLogin($initialize = false) {
        if ($initialize) $this->addSuccessfulIP();
        AuthUserData::resetFailedLogin($this->userData['userID']);
    }

    public function addSuccessfulIP() {
        AuthUserData::addSuccessfulIP($this->userData['userID'], $_SERVER['REMOTE_ADDR']);
    }

    public function makeSessionUserInfoHash() {
        $data = $_SERVER['HTTP_ACCEPT']
            . $_SERVER['HTTP_ACCEPT']
            . $_SERVER['HTTP_ACCEPT_LANGUAGE']
            . $_SERVER['SERVER_PROTOCOL']
            . $_SERVER['HTTP_ACCEPT_CHARSET']
            . $_SERVER['REMOTE_HOST'];

        return hash('sha1', $data);
    }
}

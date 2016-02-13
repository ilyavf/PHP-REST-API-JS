<?php

class AuthUser {
    private $userData;
    private $userRoles;
    private $userSession;
    private $activeSessionSecret;

    public function __construct() {}

    public function loadUser($authUser, $newSession = false) {
        if (AuthUserData::userExistConfirmed($authUser)) {
            $this->userData = AuthUserData::getUserData($authUser);
            $this->getUserRoles();
            $this->getUserSessions();

            if ($newSession) {
                return $this->initiateConnection();
            }

            return $this->findCurrentSession();
        }

        return false;
    }

    public function getUserData() {
        return $this->userData;
    }

    public function getUserLoginSecret() {
        return $this->userData['password'];
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

    public function createNewSession($mySecret) {
        $this->activeSessionSecret = $mySecret;
        $hashData = $this->makeSessionUserInfoHash();
        return AuthUserData::addNewSession($this->userData['userID'], $mySecret, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $hashData);
    }

    public function hasSecondFactor() {
        return false;
        //return (isset($this->userData->twoFactorType) && $this->userData->twoFactorType != _TWO_FACTOR_TYPE_NONE_ && $this->userData->lastActivity < strtotime("-" . _TIME_TO_LIVE_IN_MINUTES_ . " minute", time()));
    }

    public function initiateConnection() {
        $headers = getallheaders();
        $headers['Auth-User'];

        if (!$this->findCurrentSession()) {
            $createdSecret = base64_encode(hash('sha512', uniqid()));
            header('Auth-Secret: ' . $createdSecret);

            // todo: if 2nd factor
            if (false) {
                $secondFactor = '35356';
                $createSecret = base64_encode(hash_hmac('sha512', $createdSecret, $secondFactor, true));
                //todo: this new signature is stored as the new secret (the server should use the 2nd factor number the same way
            }

            $this->createNewSession($createdSecret);
        } else {
            $existingSecret = $this->activeSessionSecret;
            header('Auth-Secret: ' . $existingSecret);
        }

        // todo: email or sms (depending on user settings) the 2nd factor
        // todo: add Secret Header (even if there is no 2nd factor
        return true;
    }

    public function makeSessionUserInfoHash() {
        $data = $_SERVER['HTTP_ACCEPT']
            . $_SERVER['HTTP_ACCEPT_ENCODING']
            . $_SERVER['HTTP_ACCEPT']
            . $_SERVER['HTTP_ACCEPT_LANGUAGE']
            . $_SERVER['SERVER_PROTOCOL']
            . $_SERVER['HTTP_ACCEPT_CHARSET']
            . $_SERVER['REMOTE_HOST'];

        return $data;
    }
}

class AuthUserData {
    public static function getUserData($authUser) {
        $query = MySQL::getInstance()->prepare("SELECT * FROM AuthUser WHERE userName=:userName AND (emailConfirmed=1 OR phoneConfirmed=1)");
        $query->bindValue(':userName', $authUser);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public static function userExistConfirmed($authUser) {
        $query = MySQL::getInstance()->prepare("SELECT COUNT(userName) AS count FROM AuthUser WHERE userName=:userName" . (_MANDATORY_EMAIL_CONFIRMATION_ || _MANDATORY_PHONE_CONFIRMATION_ ? (_MANDATORY_EMAIL_CONFIRMATION_ && _MANDATORY_PHONE_CONFIRMATION_ ? " AND emailConfirmed=1 AND phoneConfirmed=1" : " AND (emailConfirmed=1 OR phoneConfirmed=1)") : ""));
        $query->bindValue(':userName', $authUser);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return ($temp['count'] == 1);
    }

    public static function getUserRoles($userID) {
        $query = MySQL::getInstance()->prepare("SELECT userRole FROM AuthUserRoles WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserSessions($userID) {
        $query = MySQL::getInstance()->prepare("SELECT * FROM AuthUserSessions WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delExpiredSessions($userID) {
        $query = MySQL::getInstance()->prepare("DELETE FROM AuthUserSessions WHERE userID=:userID AND NOW() >= DATE_ADD(sessionLastActive, INTERVAL :seconds SECOND)");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':seconds', _SESSION_EXPIRE_SECONDS_);
        $query->execute();
    }

    public static function userNameExists($authUser) {
        $query = MySQL::getInstance()->prepare("SELECT COUNT(userName) AS count FROM AuthUser WHERE userName=:userName");
        $query->bindValue(':userName', $authUser);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return ($temp['count'] == 1);
    }

    public static function addNewSession($userID, $sessionSecret, $sessionIP, $sessionUserAgent, $sessionUserInfoHash) {
        $query = MySQL::getInstance()->prepare("INSERT INTO AuthUserSessions (userID, sessionSecret, sessionIP, sessionUserAgent, sessionUserInfoHash, sessionLastActive) VALUES (:userID, :sessionSecret, :sessionIP, :sessionUserAgent, :sessionUserInfoHash, FROM_UNIXTIME(:sessionLastActive))");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':sessionSecret', $sessionSecret);
        $query->bindValue(':sessionIP', $sessionIP);
        $query->bindValue(':sessionUserAgent', $sessionUserAgent);
        $query->bindValue(':sessionUserInfoHash', $sessionUserInfoHash);
        $query->bindValue(':sessionLastActive', time());
        return $query->execute();
    }

    public static function updateSessionActivity($sessionID) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUserSessions SET sessionLastActive=FROM_UNIXTIME(:sessionLastActive) WHERE sessionID=:sessionID");
        $query->bindValue(':sessionLastActive', time());
        $query->bindValue(':sessionID', $sessionID);
        return $query->execute();
    }
}
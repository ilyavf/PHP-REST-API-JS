<?php

class AuthUserData {
    public static function getUserData($authUser) {
        $query = MySQL::getInstance()->prepare("SELECT userID, userName, email, phone, password, salt, emailConfirmed, phoneConfirmed, twoFactorType, securityQuestion, securityAnswer, failedLoginCount, UNIX_TIMESTAMP(failedLoginTime) AS failedLoginTime, extraKey, extraKeyType, baseLang, UNIX_TIMESTAMP(extraKeyCreated) AS extraKeyCreated, UNIX_TIMESTAMP(accountCreated) AS accountCreated FROM AuthUser WHERE userName=:userName");
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

    public static function emailExist($email) {
        $query = MySQL::getInstance()->prepare("SELECT COUNT(email) AS count FROM AuthUser WHERE email=:email");
        $query->bindValue(':email', $email);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return ($temp['count'] == 1);
    }

    public static function userExist($authUser) {
        $query = MySQL::getInstance()->prepare("SELECT COUNT(userName) AS count FROM AuthUser WHERE userName=:userName");
        $query->bindValue(':userName', $authUser);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return ($temp['count'] == 1);
    }

    public static function getUserByEmail($email) {
        $query = MySQL::getInstance()->prepare("SELECT userName FROM AuthUser WHERE email=:email");
        $query->bindValue(':email', $email);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return $temp['userName'];
    }

    public static function getUserRoles($userID) {
        $query = MySQL::getInstance()->prepare("SELECT userRole FROM AuthUserRoles WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserSessions($userID) {
        $query = MySQL::getInstance()->prepare("SELECT * FROM AuthUserSessions WHERE userID=:userID ORDER BY sessionID DESC");
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
        $query->execute();
        return MySQL::getInstance()->lastInsertId();
    }

    public static function addNewUser($userName, $email, $password, $salt, $securityQuestion, $securityAnswer) {
        $query = MySQL::getInstance()->prepare("INSERT INTO AuthUser (userName, email, password, salt, securityQuestion, securityAnswer, accountCreated) VALUES (:userName, :email, :password, :salt, :securityQuestion, :securityAnswer, FROM_UNIXTIME(:accountCreated))");
        $query->bindValue(':userName', $userName);
        $query->bindValue(':email', $email);
        $query->bindValue(':password', $password);
        $query->bindValue(':salt', $salt);
        $query->bindValue(':securityQuestion', $securityQuestion);
        $query->bindValue(':securityAnswer', $securityAnswer);
        $query->bindValue(':accountCreated', time());
        $query->execute();
        return MySQL::getInstance()->lastInsertId();
    }

    public static function addUserRole($userID, $userRole) {
        $query = MySQL::getInstance()->prepare("INSERT INTO AuthUserRoles (userID, userRole) VALUES (:userID, :userRole) ON DUPLICATE KEY UPDATE userRole=:userRole");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':userRole', $userRole);
        return $query->execute();
    }

    public static function addSuccessfulIP($userID, $ipAddress) {
        $query = MySQL::getInstance()->prepare("INSERT INTO AuthUserSuccessfuIPs (userID, ipAddress, useCount, lastUsed) VALUES (:userID, :ipAddress, :useCount, FROM_UNIXTIME(:lastUsed)) ON DUPLICATE KEY UPDATE lastUsed=FROM_UNIXTIME(:lastUsed), useCount=useCount+1");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':ipAddress', $ipAddress);
        $query->bindValue(':useCount', 1);
        $query->bindValue(':lastUsed', time());
        return $query->execute();
    }

    public static function updateSessionActivity($sessionID) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUserSessions SET sessionLastActive=FROM_UNIXTIME(:sessionLastActive) WHERE sessionID=:sessionID");
        $query->bindValue(':sessionLastActive', time());
        $query->bindValue(':sessionID', $sessionID);
        return $query->execute();
    }

    public static function updateFailedLogin($userID, $failedLoginCount, $failedLoginTime) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET failedLoginCount=:failedLoginCount, failedLoginTime=FROM_UNIXTIME(:failedLoginTime) WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':failedLoginCount', $failedLoginCount);
        $query->bindValue(':failedLoginTime', $failedLoginTime);
        return $query->execute();
    }
    public static function resetFailedLogin($userID) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET failedLoginCount=:failedLoginCount, failedLoginTime=:failedLoginTime WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':failedLoginCount', null);
        $query->bindValue(':failedLoginTime', null);
        return $query->execute();
    }

    public static function updateExtraKey($userID, $extraKey, $extraKeyType) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET extraKey=:extraKey, extraKeyType=:extraKeyType, extraKeyCreated=FROM_UNIXTIME(:extraKeyCreated) WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':extraKey', $extraKey);
        $query->bindValue(':extraKeyType', $extraKeyType);
        $query->bindValue(':extraKeyCreated', time());
        return $query->execute();
    }

    public static function updateLanguage($userID, $lang) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET baseLang=:baseLang WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':baseLang', $lang);
        return $query->execute();
    }

    public static function clearExtraKey($userID) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET extraKey=:param, extraKeyType=:param, extraKeyCreated=:param WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':param', null);
        return $query->execute();
    }

    public static function confirmEmail($userID) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET emailConfirmed=:param WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':param', 1);
        return $query->execute();
    }

    public static function updatePasswordAndSalt($userID, $password, $salt) {
        $query = MySQL::getInstance()->prepare("UPDATE AuthUser SET password=:password, salt=:salt WHERE userID=:userID");
        $query->bindValue(':userID', $userID);
        $query->bindValue(':password', $password);
        $query->bindValue(':salt', $salt);
        return $query->execute();
    }
}
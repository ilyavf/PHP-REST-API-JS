<?php

class EmailTemplatesData {

    public static function getEmailVariable($emailKey, $emailLang) {
        $query = MySQL::getInstance()->prepare("SELECT emailText FROM EmailVariables WHERE emailKey=:emailKey AND emailLang=:emailLang");
        $query->bindValue(':emailKey', $emailKey);
        $query->bindValue(':emailLang', $emailLang);
        $query->execute();
        $temp = $query->fetch(PDO::FETCH_ASSOC);
        return $temp['emailText'];
    }
}
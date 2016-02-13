SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `AuthUser` (
  `userID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(31) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `emailConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `phoneConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `twoFactorType` tinyint(4) NOT NULL DEFAULT '0',
  `accountCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `AuthUserRoles` (
  `userID` bigint(20) NOT NULL,
  `userRole` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userID`,`userRole`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `AuthUserSessions` (
  `userID` bigint(20) NOT NULL,
  `sessionID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sessionSecret` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `sessionIP` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `sessionUserAgent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sessionUserInfoHash` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sessionStartTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sessionLastActive` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`userID`,`sessionID`),
  UNIQUE KEY `sessionID` (`sessionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

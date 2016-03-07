-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Mar 07, 2016 at 03:22 PM
-- Server version: 5.5.48-cll
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rest_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `AuthUser`
--

CREATE TABLE IF NOT EXISTS `AuthUser` (
  `userID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(31) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `emailConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `phoneConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `twoFactorType` tinyint(4) NOT NULL DEFAULT '0',
  `securityQuestion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `securityAnswer` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `failedLoginCount` tinyint(4) DEFAULT NULL,
  `failedLoginTime` timestamp NULL DEFAULT NULL,
  `extraKey` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extraKeyType` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `extraKeyCreated` timestamp NULL DEFAULT NULL,
  `baseLang` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `accountCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Table structure for table `AuthUserRoles`
--

CREATE TABLE IF NOT EXISTS `AuthUserRoles` (
  `userID` bigint(20) NOT NULL,
  `userRole` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`userID`,`userRole`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AuthUserSessions`
--

CREATE TABLE IF NOT EXISTS `AuthUserSessions` (
  `userID` bigint(20) NOT NULL,
  `sessionID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sessionSecret` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sessionIP` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `sessionUserAgent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sessionUserInfoHash` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sessionStartTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sessionLastActive` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`userID`,`sessionID`),
  UNIQUE KEY `sessionID` (`sessionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=252 ;

-- --------------------------------------------------------

--
-- Table structure for table `AuthUserSuccessfuIPs`
--

CREATE TABLE IF NOT EXISTS `AuthUserSuccessfuIPs` (
  `userID` bigint(20) NOT NULL,
  `ipAddress` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `useCount` int(11) NOT NULL,
  `lastUsed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`,`ipAddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `EmailVariables`
--

CREATE TABLE IF NOT EXISTS `EmailVariables` (
  `emailKey` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `emailLang` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `emailText` varchar(511) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`emailKey`,`emailLang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

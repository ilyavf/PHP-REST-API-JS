<?php

class MySQL {
	private static $instance = NULL;

	private function __construct() { }
	private function __clone() { }

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new PDO('mysql:host=localhost;dbname=' . _MYSQL_DB_NAME_, _MYSQL_USER_NAME_, _MYSQL_PASSWORD_, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone='+0:00', wait_timeout=28800"));
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return self::$instance;
	}
}

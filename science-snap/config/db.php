<?php

$DB_HOST = 'localhost';
$DB_NAME = 'science_snap';
$DB_USER = 'root';
$DB_PASSWORD = '';
$DB_CHARSET = 'utf8';

function getDBConnection() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;
	try {
		$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
		return new PDO($dsn, $DB_USER, $DB_PASSWORD);
	} catch (PDOException $e) {
		die('Database connection failed: ' . $e->getMessage());
	}
}

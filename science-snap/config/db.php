<?php
/**
 * Edgar, Jamie, Noah, Jacob
 * Date Created: 2026-03-22
 * Description: Database configuration file - stores database connection parameters and getDBConnection function
 */

$DB_HOST = 'localhost';
$DB_NAME = 'science_snap';
$DB_USER = 'root';
$DB_PASSWORD = '';
$DB_CHARSET = 'utf8';

/**
 * Establishes a connection to the MySQL database using PDO
 *
 * @returns PDO - A PDO database connection object
 */
function getDBConnection() {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_CHARSET;
	try {
		$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
		return new PDO($dsn, $DB_USER, $DB_PASSWORD);
	} catch (PDOException $e) {
		die('Database connection failed: ' . $e->getMessage());
	}
}

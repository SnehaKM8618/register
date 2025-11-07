<?php
// config.php - DB credentials. Update if needed.
$DB_HOST = '127.0.0.1';
$DB_NAME = 'registration_db';
$DB_USER = 'root';
$DB_PASS = ''; // default for XAMPP; on some installs use 'root'

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

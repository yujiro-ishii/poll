<?php

ini_set('display_errors', 1);

define('DSN', 'mysql:host=localhost;dbname=dotinstall_poll_php');
difine('DB_USERNAME', 'dbuser');
define('DB_PASSWORD', 'password');

session_start();

require_once(__DIR__ . '/funciton.php')
<?php

$Host = getenv('DB_HOST') ?: 'db';
$Name = getenv('MYSQL_DATABASE') ?: 'MYSQL_DATABASE';
$User = getenv('MYSQL_USER') ?: 'MYSQL_USER';
$Pass = getenv('MYSQL_PASSWORD') ?: 'MYSQL_PASSWORD';
$Style = '/config/style.css';

$PHP_SELF = $_SERVER['PHP_SELF'];
define('WEBSTAT', false);
define('GOOGLE', false);
?>

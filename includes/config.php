<?php
session_start();

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'planeta_mebeli');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Настройки сайта
define('SITE_NAME', 'Планета Мебели');
define('SITE_URL', 'http://localhost/planeta-mebeli/');
define('ADMIN_EMAIL', 'admin@planeta-mebeli.ru');

// Временная зона
date_default_timezone_set('Europe/Moscow');

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
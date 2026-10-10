<?php

require_once __DIR__ . '/config.php';

$db_host     = $_ENV['DB_HOST'];
$db_user     = $_ENV['DB_USER'];
$db_pass     = $_ENV['DB_PASS'];
$db_database = $_ENV['DB_DATABASE'];

@$link = mysqli_connect($db_host, $db_user, $db_pass, $db_database);

if (!mysqli_connect_errno()) {
    mysqli_query($link, 'SET NAMES utf8');
} else {
    echo 'Ошибка подключения к базе данных';
    exit;
}
?>
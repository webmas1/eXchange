<?php

if (!function_exists('db_connect')) {

    function db_connect() {
        require_once 'config/database.php';

        if (!$link = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PWD, MYSQL_DB)) {
            die('Error connecting to mysql server!');
        }

        mysqli_set_charset($link, "utf8");

        return $link;
    }

}

?>


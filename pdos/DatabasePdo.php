<?php

//DB 정보 php와 mysql 연결
function pdoSqlConnect()
{
    try {
        $DB_HOST = "3.34.251.89";
        $DB_NAME = "carrotmarket_db";
        $DB_USER = "jody";
        $DB_PW = "wtisgkxb6173";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}
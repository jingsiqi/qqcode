<?php

//配置数据库
$dbserver = "172.17.2.14";
$dbusername = "root";
$dbpassword = "root123456";
//$database = $_GET["db"] ?? 'zdlh_api';
//$dsn = 'mysql:dbname='.$database.';host='.$dbserver;
$dsn = 'mysql:host=' . $dbserver;

try {
    $dbh = new PDO($dsn, $dbusername, $dbpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES'utf8';"));
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
$sql = 'show databases';
$database_list = $dbh->prepare($sql);
$database_list->execute();

//不显示的database_name
$db_ignore = array(
    'information_schema',
    'mysql',
    'performance_schema');

//db列表
while ($row = $database_list->fetch(PDO::FETCH_NUM)) {
    if (!(in_array($row[0], $db_ignore))) {
        $db [] = $row [0];
    }
}
include "index.html";
?>
<?php
$host = 'localhost';  
$port = '3307';
$user = 'piaowu_com';
$password = '123456';
$database = 'piaowu_com';

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}
?> 
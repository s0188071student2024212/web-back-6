<?php
// db_connection.php
$user = 'u68851'; 
$password = '5595263'; 
try {
    $pdo = new PDO('mysql:host=localhost;dbname=u68851', $user, $password,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("<p style='color:red;'>Ошибка подключения к базе данных: " . $e->getMessage() . "</p>");
}
?>
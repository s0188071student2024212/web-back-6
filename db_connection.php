<?php
// db_connection.php
$user = 'u68851'; 
$password = '5595263'; 
try {
    $pdo = new PDO('mysql:host=localhost;dbname=u68851', $user, $password,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Проверяем наличие поля is_admin в таблице users
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Добавляем поле is_admin, если его нет
        $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {
    die("<p style='color:red;'>Ошибка подключения к базе данных: " . $e->getMessage() . "</p>");
}
?>

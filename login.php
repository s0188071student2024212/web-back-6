<?php
session_start(); 
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    // Сначала проверяем, является ли пользователь администратором
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
    $stmt->execute([$login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Если это администратор
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['user_type'] = 'admin';
        header("Location: adminpage.php"); // Перенаправляем на страницу администратора
        exit();
    } 
    else {
        // Если не администратор, проверяем обычного пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Если это обычный пользователь
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = 'user';
            header("Location: edit.php"); // Перенаправляем на страницу редактирования данных
            exit();
        } 
        else {
            echo "<p style='color:red;'>Неверный логин или пароль.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Вход</title>
</head>
<body>
    <div id="hform">
        <form method="POST" action="">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="login" required>
            <br>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <input id="sendbutton" type="submit" value="Войти">
        </form>
    </div>
</body>
</html>

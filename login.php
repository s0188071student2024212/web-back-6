<?php
session_start(); 
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id']; // Устанавливаем ID пользователя в сессии
        header("Location: edit.php"); // Перенаправляем на страницу редактирования данных
        exit();
    } else {
        echo "<p style='color:red;'>Неверный логин или пароль.</p>";
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
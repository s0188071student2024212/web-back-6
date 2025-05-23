<?php
session_start(); 
require_once 'db_connection.php';

// Проверяем, есть ли администратор в базе данных
$stmt = $pdo->query("SELECT * FROM users WHERE is_admin = 1 LIMIT 1");
$admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Если администратора нет, создаем его (логин: admin, пароль: admin123)
if (!$admin_user) {
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (login, password, fio, phone, email, dob, gender, bio, is_admin) 
                VALUES ('admin', '$hashedPassword', 'Администратор', '0000000000', 'admin@example.com', '1970-01-01', 'male', 'Системный администратор', 1)");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_admin']) {
            $_SESSION['admin_logged_in'] = true;
            header("Location: admin.php"); // Перенаправляем в админ-панель
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: edit.php"); // Перенаправляем обычного пользователя
        }
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

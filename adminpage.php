<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); 
    exit();
}

require_once 'db_connection.php';

// Запрос для получения данных пользователей
$stmt = $pdo->prepare("
    SELECT 
        u.user_id, u.login, u.fio, u.phone, u.email, u.dob, u.gender, u.bio,
        GROUP_CONCAT(l.lang_name ORDER BY l.lang_name SEPARATOR ', ') AS langs
    FROM users u
    LEFT JOIN users_languages ul ON u.user_id = ul.user_id
    LEFT JOIN langs l ON ul.lang_id = l.lang_id
    GROUP BY u.user_id
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Страница администратора</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
    <script>
        function confirmDelete(userId) {
            if (confirm("Вы уверены, что хотите удалить пользователя?")) {
                window.location.href = 'delete_user.php?user_id=' + userId;
            }
        }
    </script>
</head>
<body>

<h1>Страница администратора</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Логин</th>
        <th>ФИО</th>
        <th>Телефон</th>
        <th>Email</th>
        <th>Дата рождения</th>
        <th>Пол</th>
        <th>О себе</th>
        <th>Любимые ЯП</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo $user['login']; ?></td>
            <td><?php echo $user['fio']; ?></td>
            <td><?php echo $user['phone']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['dob']; ?></td>
            <td><?php echo $user['gender']; ?></td>
            <td><?php echo $user['bio']; ?></td>
            <td><?php echo $user['langs']; ?></td>
            <td>
                <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>">Редактировать</a>
                <button onclick="confirmDelete(<?php echo $user['user_id']; ?>)">Удалить</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
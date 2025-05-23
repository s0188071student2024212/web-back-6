<?php
session_start();
require_once 'db_connection.php';

// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Обработка действий администратора
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        try {
            // Удаляем связанные записи о языках пользователя
            $stmt = $pdo->prepare("DELETE FROM users_languages WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Удаляем самого пользователя
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $_SESSION['message'] = "Пользователь успешно удален";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при удалении пользователя: " . $e->getMessage();
        }
    }
}

// Получаем список всех пользователей
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Для каждого пользователя получаем его языки программирования
foreach ($users as &$user) {
    $stmt_langs = $pdo->prepare("SELECT l.lang_name FROM langs l 
                               JOIN users_languages ul ON l.lang_id = ul.lang_id 
                               WHERE ul.user_id = ?");
    $stmt_langs->execute([$user['user_id']]);
    $user['languages'] = $stmt_langs->fetchAll(PDO::FETCH_COLUMN);
}
unset($user); // Разрываем ссылку
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container {
            width: 90%;
            margin: 20px auto;
        }
        .user-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .user-actions {
            margin-top: 10px;
        }
        .message {
            color: green;
            margin: 10px 0;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>Админ-панель</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="users-list">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <h3><?php echo htmlspecialchars($user['fio']); ?></h3>
                    <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['login']); ?></p>
                    <p><strong>Телефон:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Дата рождения:</strong> <?php echo htmlspecialchars($user['dob']); ?></p>
                    <p><strong>Пол:</strong> <?php echo $user['gender'] == 'male' ? 'Мужской' : 'Женский'; ?></p>
                    <p><strong>Языки программирования:</strong> <?php echo implode(', ', $user['languages']); ?></p>
                    <p><strong>Биография:</strong> <?php echo htmlspecialchars($user['bio']); ?></p>
                    
                    <div class="user-actions">
                        <a href="edit_user.php?user_id=<?php echo $user['user_id']; ?>" class="btn-edit">Редактировать</a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <button type="submit" name="delete_user" class="btn-delete">Удалить</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="logout.php" class="btn-logout">Выйти из админ-панели</a>
        </div>
    </div>
</body>
</html>

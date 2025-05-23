<?php
session_start();
require_once 'db_connection.php';

// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "Пользователь не найден";
    header("Location: admin.php");
    exit();
}

// Получаем выбранные языки пользователя
$stmt_user_langs = $pdo->prepare("SELECT lang_id FROM users_languages WHERE user_id = ?");
$stmt_user_langs->execute([$user_id]);
$user_langs_ids = $stmt_user_langs->fetchAll(PDO::FETCH_COLUMN);

// Получаем соответствие lang_id => lang_name из таблицы langs
$stmt_langs = $pdo->query("SELECT lang_id, lang_name FROM langs");
$lang_map = [];
while ($lang = $stmt_langs->fetch(PDO::FETCH_ASSOC)) {
    $lang_map[$lang['lang_id']] = $lang['lang_name'];
}

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fio = $_POST['fio'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $bio = $_POST['bio'];
    $languages = isset($_POST['languages']) ? $_POST['languages'] : [];
    
    try {
        // Обновляем данные пользователя
        $stmt = $pdo->prepare("UPDATE users SET fio = ?, phone = ?, email = ?, dob = ?, gender = ?, bio = ? WHERE user_id = ?");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $user_id]);
        
        // Обновляем языки программирования
        $stmt_delete = $pdo->prepare("DELETE FROM users_languages WHERE user_id = ?");
        $stmt_delete->execute([$user_id]);
        
        foreach ($languages as $language) {
            $stmt_lang = $pdo->prepare("SELECT lang_id FROM langs WHERE lang_name = ?");
            $stmt_lang->execute([$language]);
            $lang_result = $stmt_lang->fetch(PDO::FETCH_ASSOC);
            $lang_id = $lang_result['lang_id'];
            $stmt_user_lang = $pdo->prepare("INSERT INTO users_languages (user_id, lang_id) VALUES (?, ?)");
            $stmt_user_lang->execute([$user_id, $lang_id]);
        }
        
        $_SESSION['message'] = "Данные пользователя успешно обновлены";
        header("Location: admin.php");
        exit();
    } catch (PDOException $e) {
        $error = "Ошибка при обновлении данных: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .edit-container {
            width: 80%;
            margin: 20px auto;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>Редактирование пользователя: <?php echo htmlspecialchars($user['fio']); ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div>
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" value="<?php echo htmlspecialchars($user['fio']); ?>" required>
            </div>
            <div>
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div>
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
            </div>
            <div>
                <label>Пол:</label>
                <input type="radio" id="male" name="gender" value="male" <?php if ($user['gender'] == 'male') echo 'checked'; ?>>
                <label for="male">Мужской</label>
                <input type="radio" id="female" name="gender" value="female" <?php if ($user['gender'] == 'female') echo 'checked'; ?>>
                <label for="female">Женский</label>
            </div>
            <div>
                <label>Языки программирования:</label><br>
                <select name="languages[]" id="languages" multiple required>
                    <?php foreach ($lang_map as $lang_id => $lang_name): ?>
                        <option value="<?php echo $lang_name; ?>" <?php if (in_array($lang_id, $user_langs_ids)) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($lang_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" required><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <div>
                <input type="submit" value="Сохранить">
                <a href="admin.php" style="margin-left: 10px;">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>

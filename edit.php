<?php
session_start(); 
require_once 'db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Если не авторизован, перенаправляем на вход
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// Получаем выбранные языки пользователя
$stmt_user_langs = $pdo->prepare("SELECT lang_id FROM users_languages WHERE user_id = ?");
$stmt_user_langs->execute([$user_id]);
$user_langs_ids = $stmt_user_langs->fetchAll(PDO::FETCH_COLUMN); // Получаем массив lang_id
// Получаем соответствие lang_id => lang_name из таблицы langs
$stmt_langs = $pdo->query("SELECT lang_id, lang_name FROM langs");
$lang_map = []; // Ассоциативный массив: lang_id => lang_name
while ($lang = $stmt_langs->fetch(PDO::FETCH_ASSOC)) {
    $lang_map[$lang['lang_id']] = $lang['lang_name'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование данных</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Редактирование данных</h2>
<div id="hform">
<form id="form" action="update.php" method="POST">
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
            <?php
            foreach ($lang_map as $lang_id => $lang_name):
                $selected = in_array($lang_id, $user_langs_ids) ? 'selected' : ''; // Проверка выбранного языка
            ?>
                <option value="<?php echo $lang_name; ?>" <?php echo $selected; ?>>
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
        <input type="checkbox" id="agreement" name="agreement" required>
        <label for="agreement">С контрактом ознакомлен(а)</label>
    </div>
    <input type="submit" value="Сохранить">
</form>
</div>
</body>
</html>
<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'db_connection.php';
function sanitize($data) {
  $data = trim($data);    // удаляет пробелы в начале и конце строки
  $data = stripslashes($data);    //удаляет экранирующие слеши (\) из строки
  $data = htmlspecialchars($data);    //преобразует специальные HTML-символы в их HTML-сущности
  return $data;
}
// Функция валидации
function validate_form($data) {
  $errors = [];  //ассоциативный массив ошибок
  // Валидация ФИО:
  $fio = sanitize($data['fio']);
  if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/u", $fio)) {
      $errors['fio'] = "ФИО должно содержать только буквы и пробелы.";
  }
  if (strlen($fio) > 150) {
      $errors['fio'] = "ФИО не должно превышать 150 символов.";
  }
  // Валидация телефона
  $phone = sanitize($data['phone']);
  if (!preg_match("/^[0-9\+\-\(\)\s]+$/", $phone)) {
      $errors['phone'] = "Некорректный формат телефона.";
  }
  // Валидация email
  $email = sanitize($data['email']);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = "Некорректный формат email.";
  }
  // Валидация даты
  $dob = sanitize($data['dob']);
  if (empty($dob)) {
      $errors['dob'] = "Дата рождения обязательна для заполнения.";
  }
  // Валидация пола
  $gender = sanitize($data['gender']);
  if (!in_array($gender, ['male', 'female'])) {
      $errors['gender'] = "Некорректное значение пола.";
  }
  // Валидация ЯП
  $languages = isset($data['languages']) ? $data['languages'] : [];
  $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
  if (is_array($languages)) {
    foreach ($languages as $language) {
        if (!in_array($language, $allowed_languages)) {
            $errors['languages'] = "Недопустимый язык программирования.";
            break; 
        }
    }
  }
  // Валидация биографии
  $bio = sanitize($data['bio']);
  if (empty($bio)) {
      $errors['bio'] = "Биография обязательна для заполнения.";
  }
  // Валидация чекбокса
  if (!isset($data['agreement'])) {
      $errors['agreement'] = "Необходимо согласиться с условиями.";
  }
  // Сохраняем данные в cookies для отображения при ошибках
  setcookie('fio', $fio, time() + 365 * 24 * 60 * 60); // На год
  setcookie('phone', $phone, time() + 365 * 24 * 60 * 60);
  setcookie('email', $email, time() + 365 * 24 * 60 * 60);
  setcookie('dob', $dob, time() + 365 * 24 * 60 * 60);
  setcookie('gender', $gender, time() + 365 * 24 * 60 * 60);
  setcookie('bio', $bio, time() + 365 * 24 * 60 * 60);
  setcookie('languages', serialize($languages), time() + 365 * 24 * 60 * 60);

  return $errors;
}
// Генерация случайного логина и пароля
function generateLoginAndPassword() {
    $login = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 8);
    $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*"), 0, 12);
    return [$login, $password];
}
// Функция для получения значения из Cookie (с проверкой на существование)
function get_cookie_value($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
}
// Функция для получения значения из Cookie для языков (с десериализацией)
function get_cookie_languages() {
    return isset($_COOKIE['languages']) ? unserialize($_COOKIE['languages']) : [];
}
// Обработка POST-запроса
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = validate_form($_POST);
    if (empty($errors)) {
      try {
          list($login, $password) = generateLoginAndPassword();
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Хэшируем пароль
          $fio = sanitize($_POST['fio']);
          $phone = sanitize($_POST['phone']);
          $email = sanitize($_POST['email']);
          $dob = sanitize($_POST['dob']);
          $gender = sanitize($_POST['gender']);
          $bio = sanitize($_POST['bio']);

          // Вставка данных в таблицу users
          $stmt = $pdo->prepare("INSERT INTO users (login, password, fio, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
          $stmt->execute([$login, $hashedPassword, $fio, $phone, $email, $dob, $gender, $bio]);
          $user_id = $pdo->lastInsertId(); //получаем id текущего пользователя
          // Вставка данных в таблицу users_languages
          $languages = isset($_POST['languages']) ? $_POST['languages'] : [];
          foreach ($languages as $language) {
            $stmt_lang = $pdo->prepare("SELECT lang_id FROM langs WHERE lang_name = ?");
            $stmt_lang->execute([$language]);
            $lang_result = $stmt_lang->fetch(PDO::FETCH_ASSOC);
            $lang_id = $lang_result['lang_id'];
            $stmt_user_lang = $pdo->prepare("INSERT INTO users_languages (user_id, lang_id) VALUES (?, ?)");
            $stmt_user_lang->execute([$user_id, $lang_id]);
          }
          // Удаление cookies с ошибками (если были)
          setcookie('fio', '', time() - 3600);
          setcookie('phone', '', time() - 3600);
          setcookie('email', '', time() - 3600);
          setcookie('dob', '', time() - 3600);
          setcookie('gender', '', time() - 3600);
          setcookie('bio', '', time() - 3600);
          setcookie('languages', '', time() - 3600);
          setcookie('errors', '', time() - 3600);

          echo "<body style='
                margin-top: 20px;
                font-family: sans-serif;
                background-color: #e6e6fa;
                display: flex;
                flex-direction: column;
                align-items: center;
                background-image: url('fon.jpg');
                background-repeat: repeat;
                background-size: 40%;>";
          echo "<div style='text-align: center;'>
                <p style='color:green; 
                font-size: larger;'>
                Данные успешно сохранены!</p></div>";
          echo "<div
                    style=' text-align: center;
                    background-color: #b3a0e5;
                    border-top: 2px solid #581573;
                    border-left: 2px solid #581573;
                    border-right: 2px solid #581573;
                    width: 50%'>
                <p>Ваш логин: " . htmlspecialchars($login) . "</p></div>";
          echo "<div style='
                    text-align: center;
                    background-color: #b3a0e5;
                    border-bottom: 2px solid #581573;
                    border-left: 2px solid #581573;
                    border-right: 2px solid #581573;
                    width: 50%;
                    margin-bottom: 10px;'>
                <p>Ваш пароль: " . htmlspecialchars($password) . "</p></div>";
          echo "<div> <a style='
                text-decoration: none;
                color: #581573;
                font-size: medium;'
                href='mainpage.html'>Вернуться на главную страницу</a> </div>";
          echo "</body>";
          // Старт сессии и установка user_id
          session_start();
          $_SESSION['user_id'] = $user_id;
          exit();

      } catch (PDOException $e) {
          die("<p style='color:red;'>Ошибка сохранения данных: " . $e->getMessage() . "</p>");
      }
    } 
    else {
        // Сохраняем ошибки в cookie
        setcookie('errors', serialize($errors), time() + 3600);  // Cookie на сессию
        // Перезагружаем страницу методом GET
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
      }
 }

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Задание 5</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error {
            color: red;
        }
        .error-field {
            border: 1px solid red;
        }
    </style>
</head>
<body>

    <?php
        // Вывод сообщения об успехе
        if (isset($_COOKIE['errors'])) {
            $errors = unserialize($_COOKIE['errors']);
            echo "<div class='error'>";
            foreach ($errors as $value) {
                echo "<p>$value</p>";
            }
            echo "</div>";
            setcookie('errors', '', time() - 3600);
        }
        else {
            $errors = [];
        } 

    ?>
    <div id="hform">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div>
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" value="<?php echo get_cookie_value('fio'); ?>"
                    <?php if (isset($errors['fio'])): ?>class="error-field"<?php endif; ?>><br>
                <?php if (isset($errors['fio'])): ?>
                    <span class="error"><?php echo $errors['fio']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" value="<?php echo get_cookie_value('phone'); ?>"
                    <?php if (isset($errors['phone'])): ?>class="error-field"<?php endif; ?>><br>
                <?php if (isset($errors['phone'])): ?>
                    <span class="error"><?php echo $errors['phone']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo get_cookie_value('email'); ?>"
                    <?php if (isset($errors['email'])): ?>class="error-field"<?php endif; ?>><br>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" value="<?php echo get_cookie_value('dob'); ?>"
                    <?php if (isset($errors['dob'])): ?>class="error-field"<?php endif; ?>><br>
                <?php if (isset($errors['dob'])): ?>
                    <span class="error"><?php echo $errors['dob']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label>Пол:</label>
                <input type="radio" id="male" name="gender" value="male" <?php if (get_cookie_value('gender') == 'male') echo 'checked'; ?>
                    <?php if (isset($errors['gender'])): ?>class="error-field"<?php endif; ?>>
                <label for="male">Мужской</label>
                <input type="radio" id="female" name="gender" value="female" <?php if (get_cookie_value('gender') == 'female') echo 'checked'; ?>
                    <?php if (isset($errors['gender'])): ?>class="error-field"<?php endif; ?>>
                <label for="female">Женский</label><br>
                <?php if (isset($errors['gender'])): ?>
                    <span class="error"><?php echo $errors['gender']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label>Любимые языки программирования:</label><br>
                <?php
                $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                $selected_languages = get_cookie_languages(); 
                // Обеспечиваем, что $selected_languages — массив
                if (!is_array($selected_languages)) {
                    $selected_languages = []; // Инициализируем как пустой массив
                }
                ?>
                <select name="languages[]" id="languages" multiple required>
                    <?php foreach ($languages as $language): ?>
                        <option value="<?php echo $language; ?>" <?php if (in_array($language, $selected_languages)) echo 'selected'; ?>>
                            <?php echo $language; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <span class="error"><?php echo $errors['languages']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" <?php if (isset($errors['bio'])): ?>class="error-field"<?php endif; ?>><?php echo get_cookie_value('bio'); ?></textarea><br>
                <?php if (isset($errors['bio'])): ?>
                    <span class="error"><?php echo $errors['bio']; ?></span><br>
                <?php endif; ?>
            </div>
            <div>
                <br>
                <input type="checkbox" id="agreement" name="agreement" <?php if (isset($_POST['agreement'])) echo 'checked'; ?>
                    <?php if (isset($errors['agreement'])): ?>class="error-field"<?php endif; ?>>
                <label for="agreement">С контрактом ознакомлен(а)</label><br>
                <?php if (isset($errors['agreement'])): ?>
                    <span class="error"><?php echo $errors['agreement']; ?></span><br>
                <?php endif; ?>
            </div>
            <input id="sendbutton" type="submit" value="Сохранить">

        </form>
    </div>
</body>
</html>
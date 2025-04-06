<?php
// Подключение к базе данных
$host = 'KUBSU-DEV.RU';
$dbname = 'u68691';
$username = 'u68691';
$password = '9388506';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация и очистка данных
    $errors = [];
    
    // ФИО
    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        $errors['fio'] = 'Поле ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[а-яА-ЯёЁ\s]+$/u', $fio)) {
        $errors['fio'] = 'ФИО должно содержать только русские буквы и пробелы';
    }
    
    // Телефон
    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Поле Телефон обязательно для заполнения';
    } elseif (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $phone)) {
        $errors['phone'] = 'Некорректный формат телефона';
    }
    
    // Email
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Поле E-mail обязательно для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный формат email';
    }
    
    // Дата рождения
    $dob = $_POST['dob'] ?? '';
    if (empty($dob)) {
        $errors['dob'] = 'Поле Дата рождения обязательно для заполнения';
    }
    
    // Пол
    $gender = $_POST['gender'] ?? '';
    if (empty($gender)) {
        $errors['gender'] = 'Поле Пол обязательно для заполнения';
    }
    
    // Языки программирования
    $languages = $_POST['languages'] ?? [];
    if (empty($languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    }
    
    // Биография
    $bio = trim($_POST['bio'] ?? '');
    if (empty($bio)) {
        $errors['bio'] = 'Поле Биография обязательно для заполнения';
    }
    
    // Соглашение
    $agreement = $_POST['agreement'] ?? '';
    if (empty($agreement)) {
        $errors['agreement'] = 'Необходимо ознакомиться с контрактом';
    }
    
    // Если нет ошибок, сохраняем данные в БД
    if (empty($errors)) {
        try {
            // Преобразуем массив языков в строку
            $languages_str = implode(', ', $languages);
            
            // Подготовленный запрос для вставки данных
            $stmt = $conn->prepare("INSERT INTO form_data 
                                  (fio, phone, email, dob, gender, languages, bio, agreement, created_at) 
                                  VALUES 
                                  (:fio, :phone, :email, :dob, :gender, :languages, :bio, :agreement, NOW())");
            
            // Привязка параметров
            $stmt->bindParam(':fio', $fio);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':languages', $languages_str);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':agreement', $agreement);
            
            // Выполнение запроса
            $stmt->execute();
            
            // Перенаправление после успешного сохранения
            header('Location: success.html');
            exit;
        } catch(PDOException $e) {
            die("Ошибка при сохранении данных: " . $e->getMessage());
        }
    }
}

// Если есть ошибки или это первый заход, показываем форму
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <title>Форма</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="hform">
        <form id="form" action="index.php" method="POST">
            <div>
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($fio ?? '') ?>" required>
                <span class="error" id="fio_error"><?= $errors['fio'] ?? '' ?></span>
            </div>
        
            <div>
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" required>
                <span class="error" id="phone_error"><?= $errors['phone'] ?? '' ?></span>
            </div>
        
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                <span class="error" id="email_error"><?= $errors['email'] ?? '' ?></span>
            </div>
        
            <div>
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($dob ?? '') ?>" required>
                <span class="error" id="dob_error"><?= $errors['dob'] ?? '' ?></span>
            </div>
        
            <div>
                <label>Пол:</label>
                <input type="radio" id="male" name="gender" value="male" <?= (isset($gender) && $gender === 'male') ? 'checked' : '' ?> required>
                <label for="male">Мужской</label>
                <input type="radio" id="female" name="gender" value="female" <?= (isset($gender) && $gender === 'female') ? 'checked' : '' ?> required>
                <label for="female">Женский</label>
                <span class="error" id="gender_error"><?= $errors['gender'] ?? '' ?></span>
            </div>
        
            <div>
                <label>Любимые языки программирования:</label><br>
                <select name="languages[]" multiple required>
                    <?php
                    $options = [
                        'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 
                        'Python', 'Java', 'Haskell', 'Clojure', 
                        'Prolog', 'Scala', 'Go'
                    ];
                    foreach ($options as $option) {
                        $selected = (isset($languages) && in_array($option, $languages)) ? 'selected' : '';
                        echo "<option value=\"$option\" $selected>$option</option>";
                    }
                    ?>
                </select>
                <span class="error" id="languages_error"><?= $errors['languages'] ?? '' ?></span>
            </div>
        
            <div>
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" rows="4" cols="50" required><?= htmlspecialchars($bio ?? '') ?></textarea>
                <span class="error" id="bio_error"><?= $errors['bio'] ?? '' ?></span>
            </div>
        
            <div>
                <input type="checkbox" id="agreement" name="agreement" <?= (isset($agreement) && $agreement === 'on') ? 'checked' : '' ?> required>
                <label for="agreement">С контрактом ознакомлен(а)</label>
                <span class="error" id="agreement_error"><?= $errors['agreement'] ?? '' ?></span>
            </div>
        
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>

<?php
// Установка времени жизни cookies
$session_cookie_params = session_get_cookie_params();
$session_cookie_duration = 0; // До конца сессии
$year_cookie_duration = 365 * 24 * 60 * 60; // 1 год

// Функция для очистки cookies ошибок
function clearErrorCookies() {
    $fields = ['fio', 'number', 'email', 'birthday', 'gender', 'model', 'agreement', 'comments'];
    foreach ($fields as $field) {
        setcookie("error_$field", '', time() - 3600, '/');
    }
    setcookie('form_errors', '', time() - 3600, '/');
}

// Функция для валидации данных
function validateField($value, $pattern, $errorMessage) {
    return preg_match($pattern, $value) ? true : $errorMessage;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Валидация полей с регулярными выражениями
    $validationRules = [
        'fio' => [
            'pattern' => '/^[А-ЯЁа-яё\s-]{5,50}$/u',
            'message' => 'ФИО должно содержать только кириллические буквы, пробелы и дефисы (5-50 символов)'
        ],
        'number' => [
            'pattern' => '/^\+7\s?\(\d{3}\)\s?\d{3}-\d{2}-\d{2}$/',
            'message' => 'Введите телефон в формате +7 (XXX) XXX-XX-XX'
        ],
        'email' => [
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'message' => 'Введите корректный email'
        ],
        'birthday' => [
            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
            'message' => 'Введите дату в формате ГГГГ-ММ-ДД'
        ],
        'gender' => [
            'pattern' => '/^(male|female)$/',
            'message' => 'Выберите пол'
        ],
        'model' => [
            'pattern' => '/.+/',
            'message' => 'Выберите хотя бы одну модель'
        ],
        'agreement' => [
            'pattern' => '/^on$/',
            'message' => 'Необходимо согласие на обработку данных'
        ]
    ];
    
    // Проверка каждого поля
    foreach ($validationRules as $field => $rules) {
        $value = $_POST[$field] ?? '';
        
        if ($field === 'model') {
            $value = implode(', ', (array)$value);
        }
        
        $error = validateField($value, $rules['pattern'], $rules['message']);
        
        if ($error !== true) {
            $errors[$field] = $error;
            setcookie("error_$field", $error, time() + $session_cookie_duration, '/');
        } else {
            // Сохраняем значение поля в cookie
            setcookie($field, $value, time() + $year_cookie_duration, '/');
        }
    }
    
    // Сохраняем комментарии (необязательное поле)
    if (!empty($_POST['comments'])) {
        setcookie('comments', $_POST['comments'], time() + $year_cookie_duration, '/');
    }
    
    if (!empty($errors)) {
        // Сохраняем все ошибки в одном cookie
        setcookie('form_errors', json_encode($errors), time() + $session_cookie_duration, '/');
        
        // Перенаправляем обратно на форму с ошибками
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Очищаем cookies ошибок
        clearErrorCookies();
        
        // Устанавливаем cookie успешной отправки
        setcookie('form_success', 'Ваша заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.', time() + $session_cookie_duration, '/');
        
        // Здесь обычно идет сохранение в базу данных
        
        // Перенаправляем обратно на форму с сообщением об успехе
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Если запрос не POST, перенаправляем на форму
    header('Location: index.html');
    exit;
}
?>

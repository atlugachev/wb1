<?php
$password = 'admin123'; // Ваш пароль для администратора
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Пароль: " . $password . "\n";
echo "Хеш для базы данных: " . $hashed_password;
?>
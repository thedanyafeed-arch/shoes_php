<?php
// config.php

function loadEnv($path) {
    if (!file_exists($path)) {
        die('Файл .env не найден!');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Пропускаем комментарии
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        // Разбиваем строку по первому знаку "="
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Убираем кавычки, если они есть
            $value = trim($value, '"\'');

            // Записываем в переменные окружения
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Загружаем .env
loadEnv(__DIR__ . '/.env');
?>
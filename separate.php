<?php

// Име на базата данни.
DEFINE('DB_NAME', 'bgerp');

// Потребителско име.
DEFINE('DB_USER', 'root');

// По-долу трябва да се постави реалната парола за връзка
// с базата данни на потребителят дефиниран в предходния ред
DEFINE('DB_PASS', '321');

// Сървъра за на базата данни
DEFINE('DB_HOST', 'localhost');

// Порт на базата данни
DEFINE('DB_HOST_PORT', 3306);

// абсолютен път до физическото положение на файловете
DEFINE('UPLOADS_BASE_PATH', '/home/mitko/workspace/uploads/fileman');

function getFileName (array $parts) {
    
    $res = [];
    $res['oldName'] = UPLOADS_BASE_PATH . '/' . $parts['md5'] . "_" . $parts['file_len'];
    $res['name'] = UPLOADS_BASE_PATH . '/' . substr($parts['md5'], 0, 2) . '/' . substr($parts['md5'], 2, 2) . '/' . substr($parts['md5'], 4) . "_" . $parts['file_len'];
    
    return $res;
}

$link = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_HOST_PORT);

if ($err = mysqli_connect_errno()) {
    // Грешка при свързване с MySQL сървър
    echo ("Грешка при свързване с MySQL сървър: " . $err);
    die;
}

if (!$link->select_db(DB_NAME)) {
    die ("Грешка при избиране на база");
}

$dbRes = $link->query("select md5, file_len from fileman_data");

$totalSize = 0;
$totalFiles = 0;

while ( $fData = $dbRes->fetch_array(MYSQLI_ASSOC) ) {
    $totalSize += $fData['file_len'];
    $totalFiles += 1;
    $fileName = getFileName($fData);
    if (is_file($fileName['name'])) {
        // Копираме файла в новата дестинация
    } elseif (is_file($fileName['oldName'])) {
        // Файла е от старото наименоване - копираме го в новата дестинация
        
    } else {
        // Няма файл на физическото му място
        $err[] = "Ненамерен файл: " . $fileName['name'] . ' | ' . $fileName['oldName'];
    } 
   //print_r(getFileName($fData));
}

echo ('Общо файлове: ' . $totalFiles . PHP_EOL);
echo ('Общо размер : ' . $totalSize . PHP_EOL);
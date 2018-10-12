<?php

// Име на базата данни.
DEFINE('DB_NAME', 'bgerp');

// Потребителско име.
DEFINE('DB_USER', 'root');

// По-долу трябва да се постави реалната парола за връзка
// с базата данни на потребителят дефиниран в предходния ред
DEFINE('DB_PASS', '321');

// Сървъра на базата данни
DEFINE('DB_HOST', 'localhost');

// Порт на базата данни
DEFINE('DB_HOST_PORT', 3306);

// абсолютен път до физическото положение на файловете
DEFINE('UPLOADS_BASE_PATH', '/home/mitko/workspace/uploads/fileman');

// Място на отделените файлове
DEFINE('DESTINATION_DIR', '/home/mitko/new');

function getFileName (array $parts) {
    
    $res = [];
    $res['oldName'] = $parts['md5'] . "_" . $parts['file_len'];
    $res['dir'] = substr($parts['md5'], 0, 2) . '/' . substr($parts['md5'], 2, 2);
    $res['name'] = substr($parts['md5'], 4) . "_" . $parts['file_len'];
    
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
$copied = 0;
$skipped = 0;

while ( $fData = $dbRes->fetch_array(MYSQLI_ASSOC) ) {
    $totalSize += $fData['file_len'];
    $totalFiles += 1;
    $fileName = getFileName($fData);
    if (is_file(UPLOADS_BASE_PATH . '/' . $fileName['dir'] .'/' . $fileName['name'])) {
        // Копираме файла в новата дестинация
        if (!is_dir(DESTINATION_DIR . '/' . $fileName['dir'])) {
            //die (DESTINATION_DIR . '/' . $fileName['dir']);
            mkdir(DESTINATION_DIR . '/' . $fileName['dir'] . '/', 0777, TRUE);
        }
        if (!is_file(DESTINATION_DIR . '/' . $fileName['dir'] .'/' . $fileName['name'])) {
            if (copy(UPLOADS_BASE_PATH . '/' . $fileName['dir'] .'/' . $fileName['name'], DESTINATION_DIR . '/' . $fileName['dir'] .'/' . $fileName['name'])) {
                $copied++;
                } else {
                    $err[] = "Не можа да копира: " . '/' . $fileName['dir'] .'/' . $fileName['name'];
            }
        } else {
            $skipped++;
        }
    } elseif (is_file(UPLOADS_BASE_PATH . '/' . $fileName['oldName'])) {
        // Файла е от старото наименоване - копираме го в новата дестинация
        // в oldName не е необходимо да създаваме директории
        if (!is_file(DESTINATION_DIR . '/' . $fileName['oldName'])) {
            if (copy(UPLOADS_BASE_PATH . '/' . $fileName['oldName'], DESTINATION_DIR . '/' . $fileName['oldName'])) {
                $copied++;
            } else {
                $err[] = "Не можа да копира: " . $fileName['oldName'];
            }
        } else {
            $skipped++;
        }
        
    } else {
        // Няма файл на физическото му място
        $err[] = "Ненамерен файл: " . $fileName['name'] . ' | ' . $fileName['oldName'];
    } 
   //print_r(getFileName($fData));
}

echo ('Общо файлове : ' . $totalFiles . PHP_EOL);
echo ('Общо размер  : ' . $totalSize . PHP_EOL);
echo ('Общо копирани: ' . $copied . PHP_EOL . PHP_EOL . PHP_EOL);
echo ('Общо пропуснати: ' . $skipped . PHP_EOL . PHP_EOL . PHP_EOL);
if (!empty($err)) {
    echo ('Грешки :');    
    print_r($err);
}
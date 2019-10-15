<?php
$msg='';
define('LOGIN','vk login');
define('PASS','vk pass');
define('SECUR_CODE','91234567');// если требуется 8 цифр номера телефона (по крайней мере у меня столько запросило). Например Ваш номер телефона 79123456789, то необходимо в переменную прописать промежуток от 7 до 89, то есть 91234567.
define('DB_HOST','ip');
define('DB_LOGIN','login');
define('DB_PASS','pass');

$fd = fopen("/var/www/html/autoans/logs.txt", 'a+') or die("не удалось создать файл");
$mysqli = new mysqli(DB_HOST, DB_LOGIN, DB_PASS, 'wall_bot');

 
 

 
 
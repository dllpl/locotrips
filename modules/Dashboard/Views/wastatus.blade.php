<?php
$token = 'YbUz0Z3mQnwMDd055a356297b3008a5cde8d204b079e6';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.api-messenger.com/status?token=' . $token);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=utf-8'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$result = curl_exec($ch); // Отправим запрос
curl_close($ch);
$data = json_decode($result, true); // Разберем полученный JSON в массив

if ($data['account'] == "auth")
   { echo "Подкл"; }
else
   { echo "Выкл"; }

?>
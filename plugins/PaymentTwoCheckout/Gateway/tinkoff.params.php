<?php
use NeatekTinkoff\NeatekTinkoff\NeatekTinkoff;
require_once 'tinkoff.class.php';
$tinkoff = new NeatekTinkoff(
    array(
        array(
            'TerminalKey' => '1648464513497DEMO', // Терминал
            'Password'    => 'aaz5v9gshmcq3qhe', // Пароль
        ),
        array(
            // Подключение к БД через PDO
            'db_name' => '',
            'db_host' => '',
            'db_user' => '',
            'db_pass' => '',
        ),
    )
);
<?php
$tinkoff->AddMainInfo(
            array(
                'OrderId'     => $booking->code,
                'Description' => 'Описание бронирования', // max 250
                'Language'    => app()->getLocale(),
            )
        );
        // $tinkoff->SetRecurrent(); // Указать что рекуррентный платёж, можно не указывать
        $tinkoff->AddItem(
            array(
                'Name'     => 'Бронирование тест', // Максимум 128 символов
                'Price'    => (float)$booking->pay_now*100, // В копейках
            //    "Quantity" => (float) 1.00, // Вес или количество
            //    "Tax"      => "none", // В чеке НДС
            )
        );
        $tinkoff->SetOrderEmail($request->input("email")); // Обязательно указать емайл
        $tinkoff->SetOrderMobile($request->input("phone")); // Установить мобильный телефон
        $tinkoff->SetTaxation('usn_income'); // Тип налогообложения 
        //$tinkoff->DeleteItem(0); // Можно удалить товар по индексу
        $tinkoff->Init(); // Инициализация заказа, и запись в БД если прописаны настройки
        $tinkoff->doRedirect(); // Переадресация на оплату заказа
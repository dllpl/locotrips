<?php 
        // Отправка в ватсап
		function send()
		{
         
            // форматирование номера в формат
            
            function phonewa_format($phonewa) 
            {
            	$phonewa = trim($phonewa);
            	$res = preg_replace(
            		array(
            			'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
            			'/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
            			'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
            			'/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',	
            			'/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
            			'/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',					
            		), 
            		array(
            			'7$2$3$4$5', 
            			'7$2$3$4$5', 
            			'7$2$3$4$5', 
            			'7$2$3$4$5', 	
            			'7$2$3$4', 
            			'7$2$3$4', 
            		), 
            		$phonewa
            	);
            	return $res;
            }
            $phonewasend = "+7(927) 443-34-99";
            $messagewa = "Работает!";
            // подключение к whatsapp api и отправка сообщения
            $token = 'YbUz0Z3mQnwMDd055a356297b3008a5cde8d204b079e6';
            // формируем массив данных
            $array = array(
                array(
                'chatId' => phonewa_format($phonewasend)."@c.us", // Телефон получателя
                'message' => $messagewa, // Сообщение
                 )
            );
        // запускаем метод POST
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://app.api-messenger.com/sendmessage?token=' . $token);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch); // Отправим запрос
        curl_close($ch);
        $data = json_decode($result, true); // Разберем полученный JSON в массив
        echo $result;
	}
	send();
	?>
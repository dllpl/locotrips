<?php
    namespace Modules\Sms\Core\Drivers;
    use Illuminate\Http\Request;
	use Modules\Sms\Core\Exceptions\SmsException;
	include 'FormatePhone.php';
	class SMSimpleDriver extends Driver
	{
		protected $config;
		public function __construct($config)
		{
			$this->config = $config;
		}
        
           
        // Отправка в ватсап
	    public function send()
		{
            // форматирование номера в формат
            $phonewasend = $this->recipient;
            $messagewa = $this->message;
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
        return $result;
	}
	}
	?>
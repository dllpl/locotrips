<?php

	namespace Modules\Sms\Core\Drivers;
    use Illuminate\Http\Request;
	use Modules\Sms\Core\Exceptions\SmsException;

	class TelegramDriver extends Driver
	{

		protected $config;

		public function __construct($config)
		{
			$this->config = $config;
		}

		public function send()
		{
		    $url = $this->config['url'];
		    $token = $this->config['token'];
			$chatid = 323138036;
			$data = [
				'chat_id'->$chatid,
				'text'=>$this->message
			        ];
			
			file_get_contents($url.$token."/sendMessage?". http_build_query($data) );
		}
	}
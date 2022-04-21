<?php
	return[
		'default' => env('SMS_DRIVER', ''),
		'nexmo'=>[
			'url'=>'https://rest.nexmo.com/sms/json',
			'from'=>env('SMS_NEXMO_FROM','Booking Core'),
			'key'=>env('SMS_NEXMO_KEY',''),
			'secret'=>env('SMS_NEXMO_SECRET',''),
		],
		'smsimple'=>[
			'url'=>'http://api.smsimple.ru',
			'from'=>env('SMS_SMSIMPLE_FROM',''),
			'sid'=>env('SMS_SMSIMPLE_ACCOUNTSID','halapov'),
			'token'=>env('SMS_SMSIMPLE_TOKEN','89510686613t'),
		],
	];
<?php

namespace Modules\Sms\Core;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Manager;
use Modules\Sms\Core\Drivers\NexmoDriver;
use Modules\Sms\Core\Drivers\NullDriver;
use Modules\Sms\Core\Drivers\SMSimpleDriver;

class SmsManager extends Manager
{
	public function channel($name = null)
    {
        return $this->driver($name);
    }


    public function createNexmoDriver()
    {
	    \config()->set('sms.nexmo.key',setting_item('sms_nexmo_api_key',\config('sms.nexmo.key')));
	    \config()->set('sms.nexmo.secret',setting_item('sms_nexmo_api_secret',\config('sms.nexmo.secret')));
	    \config()->set('sms.nexmo.from',setting_item('sms_nexmo_api_from',\config('sms.nexmo.from')));
        return new NexmoDriver(config('sms.nexmo'));
    }
	public function createSMSimpleDriver()
	{
		\config()->set('sms.smsimple.from',setting_item('sms_smsimple_api_from',\config('sms.smsimple.from')));
		\config()->set('sms.smsimple.sid',setting_item('sms_smsimple_account_sid',\config('sms.smsimple.sid')));
		\config()->set('sms.smsimple.token',setting_item('sms_smsimple_account_token',\config('sms.smsimple.token')));
		return new SMSimpleDriver(config('sms.smsimple'));
	}
	public function createLogDriver()
	{

		return new NullDriver;

	}

    public function createNullDriver()
    {

        return new NullDriver;
    }

    /**
     * Get the default SMS driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
	    $channel = setting_item('sms_driver');
	    Config::set('sms.default', $channel);
	    return config('sms.default','');
    }
}
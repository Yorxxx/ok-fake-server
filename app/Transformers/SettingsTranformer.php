<?php
namespace App\Transformers;

use App\Account;
use App\Setting;
use League\Fractal\TransformerAbstract;

class SettingsTranformer extends TransformerAbstract
{
    public function transform(Setting $setting)
    {
        return [
            'id'        => (int) $setting->id,
            'language'    => $setting->language,
            'email_notifications'    => (bool)$setting->email_notifications,
            'sms_notifications'    => (bool)$setting->sms_notifications,
            'app_notifications'    => (bool)$setting->app_notifications
        ];
    }
}
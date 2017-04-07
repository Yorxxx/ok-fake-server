<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['language',  'email_notifications', 'sms_notifications', 'app_notifications', 'user_id'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id'];
}

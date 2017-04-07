<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['account', 'owner', 'name', 'phone', 'prefix', 'email', 'country', 'user_id'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id'];
}

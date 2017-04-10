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
    protected $fillable = ['account', 'owner', 'name', 'phone', 'email', 'country', 'user_id'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Returns the user associated to this agent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    /**
     * Returns the received transactions from this agent
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedTransactions() {
        return $this->hasMany('App\Transaction', 'agent_destination');
    }

    /**
     * Returns the localphone (ie: phone without prefix)
     */
    public function localPhone() {
        if ($this->phone == null) {
            return null;
        }
        $phone = '';
        $phone_values = explode('-', $this->phone);
        $phone = array_values($phone_values)[1];
        return $phone;
    }

    /**
     * Returns the prefix of the related phone (or null if no phone)
     */
    public function prefix() {
        if ($this->phone == null) {
            return null;
        }
        $prefix = null;
        $phone_values = explode('-', $this->phone);
        $prefix = array_values($phone_values)[0];
        return $prefix;
    }
}

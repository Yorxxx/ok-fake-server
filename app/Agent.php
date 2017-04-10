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
}

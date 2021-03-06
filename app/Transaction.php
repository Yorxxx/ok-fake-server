<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['date_creation',  'date_start', 'date_end', 'concept', 'amount_source', 'amount_destination', 'currency_source',
        'currency_destination', 'state', 'frequency', 'sms_custom_text', 'ticket_otp', 'agent_destination', 'account_source', 'user_id'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'date_start',
        'date_end',
        'date_creation'
    ];

    /**
     * Returns the user associated
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    /**
     * Return the destination agent
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function destination() {
        return $this->hasOne('App\Agent', 'id', 'agent_destination');
    }

    /**
     * Returns the source account
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function source() {
        return $this->hasOne('App\Account', 'id', 'account_source');
    }
}

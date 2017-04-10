<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'phone'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id', 'doctype', 'document', 'password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Returns the transactions associated to this user
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Returns the agents of the user
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agents() {
        return $this->hasMany('App\Agent');
    }
}

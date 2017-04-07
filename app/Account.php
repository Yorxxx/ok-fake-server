<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['number',  'user_id', 'alias', 'linked', 'currency', 'amount', 'enterprise', 'center', 'product', 'contract_number',
                            'connection_enterprise', 'connection_center', 'connection_product', 'connection_contract_number',
                            'connection_person_type', 'connection_marco_channel', 'connection_person_code'];

    /**
     * The attributes that are not mass assignable
     * @var array
     */
    protected $guarded = ['id'];
}

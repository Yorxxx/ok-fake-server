<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    //
    /**
     * ('id', models.AutoField(serialize=False, auto_created=True, primary_key=True, verbose_name='ID')),
    ('number', models.CharField(max_length=35)),
    ('alias', models.CharField(max_length=35)),
    ('linked', models.BooleanField(default=False)),
    ('currency', models.CharField(max_length=3, choices=[('EUR', 'EUR'), ('GBD', 'GBD')])),
    ('amount', models.DecimalField(decimal_places=2, max_digits=20)),
    ('enterprise', models.CharField(max_length=10)),
    ('center', models.CharField(max_length=10)),
    ('product', models.CharField(max_length=10)),
    ('contract_number', models.CharField(max_length=15)),
    ('connection_enterprise', models.CharField(max_length=10)),
    ('connection_center', models.CharField(max_length=10)),
    ('connection_product', models.CharField(max_length=10)),
    ('connection_contract_number', models.CharField(max_length=15)),
    ('connection_person_type', models.CharField(max_length=10)),
    ('connection_marco_channel', models.CharField(max_length=10)),
    ('connection_person_code', models.CharField(max_length=10)),
    ('user', models.ForeignKey(to=settings.AUTH_USER_MODEL, related_name='accounts')),
     */
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

    /**
     * Get the user that owns this account.
     */
    /*public function user()
    {
        return $this->belongsTo('App\User');
    }*/
}

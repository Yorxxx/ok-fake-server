<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->unique();
            $table->string('alias')->nullable();
            $table->boolean('linked');
            $table->string('currency');
            $table->decimal('amount');
            $table->string('enterprise')->nullable();
            $table->string('center')->nullable();
            $table->string('product')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('connection_enterprise')->nullable();
            $table->string('connection_center')->nullable();
            $table->string('connection_product')->nullable();
            $table->string('connection_contract_number')->nullable();
            $table->string('connection_person_type')->nullable();
            $table->string('connection_marco_channel')->nullable();
            $table->string('connection_person_code')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}

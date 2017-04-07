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

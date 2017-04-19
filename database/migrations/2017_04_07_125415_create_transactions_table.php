<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concept');
            $table->decimal('amount_source');
            $table->decimal('amount_destination');
            $table->string('currency_source');
            $table->string('currency_destination');
            $table->integer('state');
            $table->integer('frequency');
            $table->string('sms_custom_text')->nullable();
            $table->string('ticket_otp')->nullable();
            $table->integer('agent_destination');
            $table->integer('account_source');
            $table->integer('user_id');
            $table->timestamp('date_creation')->nullable();
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}

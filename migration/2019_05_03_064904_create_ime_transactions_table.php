<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImeTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ime_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('MerchantCode', 10);
            $table->float('TranAmount');
            $table->string('RefId', 20);
            $table->string('TokenId', 20);
            $table->string('TransactionId', 20)->nullable();
            $table->string('Msisdn', 20)->nullable();
            $table->tinyInteger('TranStatus')->nullable();
            $table->string('StatusDetail', 200)->nullable();
            $table->string('SpecialStatus', 200)->nullable();
            $table->dateTime('RequestDate')->nullable();
            $table->dateTime('ResponseDate')->nullable();
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
        Schema::dropIfExists('ime_transactions');
    }
}

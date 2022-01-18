<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafetyVaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safety_vaults', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vaultable_id');
            $table->string('vaultable_type');
            $table->string('total_balance')->default(0);
            $table->string('available_balance')->default(0);
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
        Schema::dropIfExists('safety_vaults');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_package_id');
            $table->bigInteger('user_id');
            $table->string('business_name');
            $table->string('description');
            $table->integer('country_id');
            $table->integer('state_id');
            $table->integer('city_id');
            $table->string('address');
            $table->json('socials');
            $table->tinyInteger('status')->default(1);
            $table->integer('active')->default(0);
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
        Schema::dropIfExists('vendors');
    }
}

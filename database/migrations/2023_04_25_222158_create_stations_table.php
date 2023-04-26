<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('latitude');
            $table->double('longitude');
            $table->unsignedBigInteger('company_id');
            $table->text('address');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stations');
    }
};

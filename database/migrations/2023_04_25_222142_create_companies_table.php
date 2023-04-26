<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_company_id')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->foreign('parent_company_id')->references('id')->on('companies');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderAlertTable extends Migration
{
    public function up()
    {
        Schema::create('header_alert', function (Blueprint $table) {
            $table->id('header_alert_id');
            $table->string('cateogry')->nullable();
            $table->longText('text');
            $table->tinyInteger('valid')->nullable();

            $table->index('valid');
        });
    }

    public function down()
    {
        Schema::dropIfExists('header_alert');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeaderAlertTable extends Migration
{
    public function up()
    {
        Schema::create('header_alert', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('header_alert_id', true);
            $table->string('cateogry', 45)->nullable();
            $table->longText('text')->nullable();
            $table->tinyInteger('valid')->nullable();

            $table->index('valid', 'index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('header_alert');
    }
}

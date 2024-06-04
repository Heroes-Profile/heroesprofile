<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maps_translations', function (Blueprint $table) {
            $table->id('maps_translations_id');
            $table->string('name', 45)->collation('utf8mb4_0900_ai_ci');
            $table->string('short_name', 45)->collation('utf8mb4_0900_ai_ci');
            $table->string('translation', 45)->collation('utf8mb4_0900_ai_ci');
            $table->timestamps(); // Add this line if you want timestamps

            $table->index(['name', 'short_name', 'translation'], 'INDEX');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maps_translations');
    }
}

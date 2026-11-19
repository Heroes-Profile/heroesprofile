<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('heroes_translations', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('heroes_translations_id', true);
            $table->string('name', 45);
            $table->string('short_name', 45);
            $table->string('translation', 45);
            $table->string('attribute_id', 45);

            // Adding index
            $table->index(['name', 'short_name', 'translation', 'attribute_id'], 'INDEX');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heroes_translations');
    }
};

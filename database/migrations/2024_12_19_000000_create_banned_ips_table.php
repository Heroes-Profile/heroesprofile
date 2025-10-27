<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannedIpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('heroesprofile_logs')->create('banned_ips', function (Blueprint $table) {
            $table->increments('banned_ips_id');
            $table->string('ip', 45)->unique();
            $table->text('reason')->nullable();
            $table->timestamp('banned_at')->useCurrent();
            $table->index('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('heroesprofile_logs')->dropIfExists('banned_ips');
    }
}

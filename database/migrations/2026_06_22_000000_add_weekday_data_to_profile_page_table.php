<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeekdayDataToProfilePageTable extends Migration
{
    public function up()
    {
        Schema::connection('heroesprofile_cache')->table('profile_page', function (Blueprint $table) {
            $table->longText('weekday_data')->nullable()->after('matches');
        });
    }

    public function down()
    {
        Schema::connection('heroesprofile_cache')->table('profile_page', function (Blueprint $table) {
            $table->dropColumn('weekday_data');
        });
    }
}

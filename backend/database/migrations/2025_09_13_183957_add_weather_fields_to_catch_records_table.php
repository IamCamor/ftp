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
        Schema::table('catch_records', function (Blueprint $table) {
            // Добавляем только недостающие поля погоды
            $table->string('cloudiness', 50)->nullable()->comment('Облачность (ясно, малооблачно, облачно, пасмурно)');
            $table->string('precipitation', 50)->nullable()->comment('Осадки (без осадков, дождь, снег, град)');
            $table->string('wind_direction', 20)->nullable()->comment('Направление ветра (С, СВ, В, ЮВ, Ю, ЮЗ, З, СЗ)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catch_records', function (Blueprint $table) {
            $table->dropColumn([
                'cloudiness',
                'precipitation',
                'wind_direction'
            ]);
        });
    }
};

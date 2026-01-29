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
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['inicio_curso', 'fin_curso']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('inicio_curso')->nullable()->after('nombre');
            $table->integer('fin_curso')->nullable()->after('inicio_curso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->date('inicio_curso')->change();
            $table->date('fin_curso')->change();
        });
    }
};

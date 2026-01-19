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
            $table->date('trimestre_1_inicio')->nullable();
            $table->date('trimestre_1_fin')->nullable();
            $table->date('trimestre_2_inicio')->nullable();
            $table->date('trimestre_2_fin')->nullable();
            $table->date('trimestre_3_inicio')->nullable();
            $table->date('trimestre_3_fin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'trimestre_1_inicio',
                'trimestre_1_fin',
                'trimestre_2_inicio',
                'trimestre_2_fin',
                'trimestre_3_inicio',
                'trimestre_3_fin',
            ]);
        });
    }
};

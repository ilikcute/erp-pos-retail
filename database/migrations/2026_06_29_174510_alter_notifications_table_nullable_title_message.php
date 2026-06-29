<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan kolom title dan message nullable agar kompatibel
     * dengan Laravel built-in DatabaseNotification yang tidak mengisi
     * kedua kolom tersebut — data disimpan ke kolom `data` (JSON).
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->default(null)->change();
            $table->text('message')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->text('message')->nullable(false)->change();
        });
    }
};

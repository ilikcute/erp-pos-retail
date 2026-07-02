<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Shift Pagi", "Shift Sore"
            $table->string('shift_name')->nullable();        // Alias
            $table->time('start_time');                      // 08:00:00
            $table->time('end_time');                        // 16:00:00
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // Seed default shifts
        DB::table('cashier_shifts')->insert([
            [
                'name' => 'Shift Pagi',
                'shift_name' => 'Shift Pagi',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift Sore',
                'shift_name' => 'Shift Sore',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift Malam',
                'shift_name' => 'Shift Malam',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};

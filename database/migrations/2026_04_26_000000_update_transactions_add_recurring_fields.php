<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('recurring_frequency', 20)->nullable()->after('is_recurring');
            $table->date('recurring_end_date')->nullable()->after('recurring_frequency');
            $table->date('next_due_date')->nullable()->after('recurring_end_date');
        });

        DB::statement('UPDATE transactions SET recurring_frequency = recurring_interval');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('recurring_interval');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('recurring_interval', 20)->nullable()->after('is_recurring');
        });

        DB::statement('UPDATE transactions SET recurring_interval = recurring_frequency');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['recurring_frequency', 'recurring_end_date', 'next_due_date']);
        });
    }
};

<?php

use App\Enums\ReportStatus;
use App\Enums\ReportType;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->text('description');

            // Using ReportType::cases() to get the enum values
            $table->enum('type', ReportType::values());

            $table->enum('status', ReportStatus::values());

//            $table->unsignedBigInteger('user_id');
            $table->timestamps(); // created_at and updated_at

            // Foreign key to users table
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Foreign key to reported users table
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

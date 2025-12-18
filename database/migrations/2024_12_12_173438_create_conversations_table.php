<?php

use App\Enums\ConversationType;
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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id')->nullable(); // Nullable for group conversations
            $table->enum('type', ConversationType::values()); // Use enum for type column
            $table->string('name')->nullable(); // Optional, for group conversations to hold group name
            $table->softDeletes();
            $table->timestamps();

            // Foreign key for user-to-user conversation (match_id will be used in group conversations)
            $table->foreign('match_id')->references('id')->on('swipe_matches')->onDelete('cascade');


//            $table->id();
//            $table->unsignedBigInteger('sender_id');
//            $table->unsignedBigInteger('receiver_id');
//            $table->unsignedBigInteger('match_id');
//            $table->softDeletes();
//            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
//            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
//            $table->foreignId('match_id')->constrained('swipe_matches')->cascadeOnDelete();
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
//            $table->unsignedBigInteger('conversation_id');
//            $table->unsignedBigInteger('sender_id');
//            $table->unsignedBigInteger('receiver_id')->nullable(); // Nullable for group messages
            $table->timestamp('read_at')->nullable();
            $table->text('body')->nullable();
//            $table->enum('type', ['text', 'audio', 'video']);

            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->cascadeOnDelete(); // For group conversations, receiver_id can be null
            $table->timestamps();

//            $table->id();
//            $table->unsignedBigInteger('conversation_id');
//            $table->unsignedBigInteger('sender_id');
//            $table->unsignedBigInteger('receiver_id');
//            $table->timestamp('read_at')->nullable();
//            $table->text('content');
//            $table->enum('type', ['text', 'audio', 'video']);
//            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
//            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
//            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

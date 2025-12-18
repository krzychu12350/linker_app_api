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
        Schema::create('swipe_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swipe_id_1');
            $table->unsignedBigInteger('swipe_id_2');
            //$table->unique(['swipe_id_1', 'swipe_id_2']);

//            $table->foreignId('swipe_id_1')->constrained('swipes')->cascadeOnDelete();
//            $table->foreignId('swipe_id_2')->constrained('swipes')->cascadeOnDelete();

//            $table->foreign('swipe_id_1')->references('user_id')
//                ->on('swipes')
//                ->onDelete('cascade');
//            $table->foreign('swipe_id_2')->references('swiped_user_id')
//                ->on('swipes')
//                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swipe_matches');
    }
};

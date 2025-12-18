<?php

use App\Enums\DetailGroup;
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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Dynamically generate the enum values from DetailGroup
            $table->enum('group', DetailGroup::values());

            $table->string('sub_group')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // Self-relation
            $table->timestamps();

            // Foreign key constraint for self-relation (parent_id references id in the same table)
            $table->foreign('parent_id')->references('id')->on('details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};

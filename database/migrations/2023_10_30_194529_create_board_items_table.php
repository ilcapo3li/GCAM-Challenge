<?php

use App\Enums\Status;
use App\Enums\TaskType;
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
        Schema::create('board_items', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('title');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('board_id')->constrained('boards');
            $table->foreignId('parent_id')->nullable()->constrained('board_items');
            $table->integer('status_id')->default(Status::TODO->value);
            $table->index('status_id');
            $table->integer('type_id')->default(TaskType::TASK->value);
            $table->index('type_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_items');
    }
};

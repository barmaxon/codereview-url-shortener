<?php

use App\Models\Link;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')
                ->references('id')
                ->on('links')
                ->cascadeOnDelete();
            $table->string('user_agent');
            $table->string('ip');
            $table->index(['ip', 'user_agent', 'link_id']);
            $table->dateTime('datetime')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};

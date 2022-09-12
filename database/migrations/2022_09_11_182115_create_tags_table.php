<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('link_tags', static function (Blueprint $table) {
            $table->foreignIdFor(Tag::class);
            $table->foreignId('link_id')->references('id')->on('links')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_tags');
        Schema::dropIfExists('tags');
    }
};

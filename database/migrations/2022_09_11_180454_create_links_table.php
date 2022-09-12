<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', static function (Blueprint $table) {
            $table->id();
            $table->string('long_url', 2048)->unique();
            $table->string('shortened_uri')->unique()
                ->charset('utf8')
                ->collation('utf8_bin');
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};

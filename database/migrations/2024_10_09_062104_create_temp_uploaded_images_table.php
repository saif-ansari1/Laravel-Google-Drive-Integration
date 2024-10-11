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
        Schema::create('temp_uploaded_images', function (Blueprint $table) {
            $table->id();
            $table->string('file_id'); // Google Drive file ID
            $table->string('file_name'); // File name
            $table->string('file_url'); // URL of the file on Google Drive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_uploaded_images');
    }
};

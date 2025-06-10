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
        Schema::create('makanan', function (Blueprint $table) {
            $table->id(); // Ini akan membuat kolom 'id' sebagai primary key auto-increment
            $table->string('nama');
            $table->string('jenis');
            $table->string('rasa');
            $table->string('tingkatPedas');
            $table->string('tekstur');
            $table->string('imageUri')->nullable(); // Jika imageUri bisa kosong
            $table->timestamps(); // Menambahkan created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makanan');
    }
};

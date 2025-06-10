<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\App; // Import App facade untuk mendapatkan locale

class Makanan extends Model
{
    protected $table = 'makanan'; // Nama tabel di database

    protected $fillable = [
        'nama',
        'jenis',
        'rasa',
        'tingkatPedas',
        'tekstur',
        'imageUri',
        'user_id'
    ];

    public $timestamps = true;

    // Mengubah accessor/mutator untuk tingkatPedas agar mendukung multi-bahasa
    protected function tingkatPedas(): Attribute
    {
        return Attribute::make(
            // Bagian 'get' untuk menampilkan deskripsi dalam bahasa yang aktif
            get: fn (string $value) => match ($value) {
                'S' => __('makanan.tingkat_pedas_sangat_pedas'), // Mengambil terjemahan
                'P' => __('makanan.tingkat_pedas_pedas'),
                'A' => __('makanan.tingkat_pedas_agak_pedas'),
                'T' => __('makanan.tingkat_pedas_tidak_pedas'),
                default => __('makanan.tingkat_pedas_tidak_diketahui'),
            },
            // Bagian 'set' untuk mengkonversi input (dari berbagai bahasa) menjadi karakter tunggal sebelum disimpan
            set: fn (string $value) => match (strtolower($value)) {
                'sangat pedas', 'very spicy' => 'S',
                'pedas', 'spicy' => 'P',
                'agak pedas', 'mildly spicy' => 'A',
                'tidak pedas', 'not spicy' => 'T',
                default => null, // Jika input tidak cocok, simpan null atau sesuai default
            },
        );
    }

    protected function fullImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->imageUri ? asset('storage/makanan_images/' . $this->imageUri) : null,
        );
    }

    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->nama . ' (' . $this->jenis . ')',
        );
    }

    protected function rasaUpper(): Attribute
    {
        return Attribute::make(
            get: fn (string $value, array $attributes) => strtoupper($attributes['rasa']),
        );
    }
}


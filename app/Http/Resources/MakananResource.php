<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MakananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'nama'                   => $this->nama,
            'jenis'                  => $this->jenis,
            'rasa'                   => $this->rasa,
            'tingkat_pedas_kode'     => $this->getRawOriginal('tingkatPedas'), // Mendapatkan kode asli (S, P, A, T)
            'tingkat_pedas_deskripsi'=> $this->tingkatPedas, // Ini akan memanggil accessor yang sudah diterjemahkan
            'tekstur'                => $this->tekstur,
            'image_uri'              => $this->imageUri,
            'full_image_url'         => $this->full_image_url,           // Mengambil dari accessor di model
            'nama_lengkap_makanan'   => $this->nama_lengkap,             // Mengambil dari accessor di model

            'created_at'             => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'             => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            'links' => [
                'self' => route('makanans.show', $this->id), // Asumsi ada route 'makanan.show'
            ],
        ];
    }
}


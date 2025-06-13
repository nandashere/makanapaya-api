<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MakananResource;
use App\Models\Makanan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MakananController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->header('user_id');

        // Pastikan user_id ada sebelum melakukan query
        if (!$userId) {
            return response()->json(['message' => 'User ID header is missing.'], 400);
        }

        $makanans = Makanan::where('user_id', $userId)->get();
        return MakananResource::collection($makanans)->toResponse(request());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validasi data input
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'rasa' => 'required|string|max:255',
            // Aturan validasi tingkatPedas diperbarui untuk menerima input multilingual
            'tingkatPedas' => 'required|string|in:Tidak Pedas,Agak Pedas,Pedas,Sangat Pedas,Not Spicy,Mildly Spicy,Spicy,Very Spicy',
            'tekstur' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // 2. Ambil user_id dari header
        $userId = $request->header('user_id');

        // 3. Tambahkan validasi jika user_id dari header kosong
        if (!$userId) {
            return response()->json(['message' => 'User ID header is missing.'], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) { // Periksa apakah ada file dengan nama 'image'
            $imageFile = $request->file('image');
            // Simpan gambar ke direktori 'makanan_images' di dalam public disk (storage/app/public)
            $imagePath = $imageFile->store('makanan_images', 'public');
            // $imagePath akan berisi path relatif seperti 'makanan_images/namafileunik.jpg'
        }

        // 4. Masukkan user_id ke dalam data yang divalidasi SEBELUM membuat record
        $validatedData['user_id'] = $userId;
        $validatedData['imageUri'] = $imagePath;

        // 5. Buat record Makanan menggunakan semua data yang divalidasi
        $makanan = Makanan::create($validatedData);

        // 6. Kembalikan resource dengan status 201 Created
        return (new MakananResource($makanan))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Makanan  $makanan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Makanan $makanan): JsonResponse
    {
        $userId = request()->header('user_id');

        // Verifikasi bahwa makanan ini milik user yang meminta
        if ($makanan->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return (new MakananResource($makanan))->toResponse(request());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Makanan  $makanan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Makanan $makanan): JsonResponse
    {
        $userId = $request->header('user_id');

        // Verifikasi bahwa makanan ini milik user yang meminta
        if ($makanan->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $validatedData = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'jenis' => 'sometimes|required|string|max:255',
            'rasa' => 'sometimes|required|string|max:255',
            // Aturan validasi tingkatPedas diperbarui untuk menerima input multilingual
            'tingkatPedas' => 'sometimes|required|string|in:Tidak Pedas,Agak Pedas,Pedas,Sangat Pedas,Not Spicy,Mildly Spicy,Spicy,Very Spicy',
            'tekstur' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada dan bukan gambar default/placeholder
            if ($makanan->imageUri && Storage::disk('public')->exists($makanan->imageUri)) {
                Storage::disk('public')->delete($makanan->imageUri);
            }
            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('makanan_images', 'public');
            $validatedData['imageUri'] = $imagePath; // Perbarui path gambar di database
        } elseif (array_key_exists('imageUri', $validatedData) && $validatedData['imageUri'] === null) {
            // Logika jika imageUri secara eksplisit di-set null dari klien (untuk menghapus gambar)
            if ($makanan->imageUri && Storage::disk('public')->exists($makanan->imageUri)) {
                Storage::disk('public')->delete($makanan->imageUri);
            }
            $validatedData['imageUri'] = null; // Set imageUri di database menjadi null
        }

        $makanan->update($validatedData);
    
        return (new MakananResource($makanan))->toResponse(request());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Makanan  $makanan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Makanan $makanan): Response
    {
        $userId = request()->header('user_id');

        // Verifikasi bahwa makanan ini milik user yang meminta
        if ($makanan->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        if ($makanan->imageUri && Storage::disk('public')->exists($makanan->imageUri)) {
            Storage::disk('public')->delete($makanan->imageUri);
        }

        $makanan->delete();
        return response()->noContent();
    }
}

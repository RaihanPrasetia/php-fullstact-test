<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MyClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = MyClient::all();
        return response()->json($client);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:clients',
            'client_logo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'client_prefix' => 'required|string|max:4',
            'is_project' => 'nullable|in:0,1',
            'self_capture' => 'nullable|in:0,1',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        try {
            $path = $request->file('client_logo')->store('client_logos', 's3');
            $url = Storage::disk('s3');

            $client = MyClient::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'client_logo' => $url,
                'is_project' => $request->is_project ?? '0',
                'self_capture' => $request->self_capture ?? '1',
                'client_prefix' => strtoupper($request->client_prefix),
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'city' => $request->city,
            ]);

            // Simpan ke Redis
            Redis::set($client->slug, json_encode($client));

            return response()->json(['message' => 'Client created successfully', 'data' => $client], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        // Cek apakah data ada di Redis
        $client = Redis::get($slug);

        if ($client) {
            return response()->json(json_decode($client));
        }

        // Jika tidak ada di Redis, ambil dari database
        $client = MyClient::where('slug', $slug)->firstOrFail();

        // Simpan data ke Redis untuk penggunaan selanjutnya
        Redis::set($slug, json_encode($client));

        return response()->json($client);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $slug)
    {
        $client = MyClient::where('slug', $slug)->firstOrFail();

        // Validasi
        $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client,slug,' . $client->id,
            'client_logo' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        // Cek apakah ada file baru untuk client_logo
        if ($request->hasFile('client_logo')) {
            // Hapus logo lama di S3
            Storage::disk('s3')->delete(parse_url($client->client_logo, PHP_URL_PATH));

            // Upload logo baru ke S3
            $path = $request->file('client_logo')->store('client_logos', 's3');
            $url = Storage::disk('s3');
            $client->client_logo = $url;
        }

        // Update data client
        $client->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'is_project' => $request->is_project ?? '0',
            'self_capture' => $request->self_capture ?? '1',
            'client_prefix' => $request->client_prefix,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
        ]);

        // Update Redis dengan data baru
        Redis::set($client->slug, json_encode($client));

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $client = MyClient::where('slug', $slug)->firstOrFail();

        // Hapus Redis
        Redis::del($client->slug);

        // Soft delete client (update deleted_at)
        $client->deleted_at = now();
        $client->save();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}

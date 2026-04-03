<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class LokasiController extends Controller
{
    public function search(Request $request)
    {
        $q = strtolower(trim($request->get('q', '')));
        if (strlen($q) < 2) return response()->json([]);

        $data = $this->getData();

        $results = collect($data)
            ->filter(fn($item) => str_contains(strtolower($item), $q))
            ->take(10)
            ->values()
            ->map(fn($item) => ['label' => $item, 'value' => $item]);

        return response()->json($results);
    }

    private function getData(): array
    {
        return Cache::remember('lokasi_data', 86400, function () {
            // Try both paths (Laravel 11 may use private/)
            $paths = [
                storage_path('app/lokasi.json'),
                storage_path('app/private/lokasi.json'),
            ];
            foreach ($paths as $path) {
                if (File::exists($path)) {
                    $data = json_decode(File::get($path), true);
                    if (!empty($data)) return $data;
                }
            }
            return [];
        });
    }
}

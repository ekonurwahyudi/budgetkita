<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class DownloadLokasiData extends Command
{
    protected $signature = 'lokasi:download';
    protected $description = 'Download data kelurahan Indonesia dari API';

    public function handle()
    {
        $this->info('Downloading provinsi...');
        $provinces = Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json')->json();

        $data = [];
        $bar = $this->output->createProgressBar(count($provinces));

        foreach ($provinces as $prov) {
            $regencies = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$prov['id']}.json")->json();
            foreach ($regencies ?? [] as $reg) {
                $districts = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/{$reg['id']}.json")->json();
                foreach ($districts ?? [] as $dist) {
                    $villages = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/{$dist['id']}.json")->json();
                    foreach ($villages ?? [] as $vil) {
                        $data[] = "{$vil['name']}, {$dist['name']}, {$reg['name']}, {$prov['name']}";
                    }
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        File::put(storage_path('app/lokasi.json'), json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->info('Saved ' . count($data) . ' lokasi to storage/app/lokasi.json');
    }
}

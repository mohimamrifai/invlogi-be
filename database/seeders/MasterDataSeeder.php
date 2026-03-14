<?php

namespace Database\Seeders;

use App\Models\ContainerType;
use App\Models\Location;
use App\Models\TransportMode;
use App\Models\ServiceType;
use App\Models\AdditionalService;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Lokasi (Port/Kota awal) ──
        $locations = [
            ['name' => 'Jakarta', 'code' => 'JKT', 'type' => 'port', 'city' => 'Jakarta', 'province' => 'DKI Jakarta'],
            ['name' => 'Surabaya', 'code' => 'SUB', 'type' => 'port', 'city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Semarang', 'code' => 'SMG', 'type' => 'port', 'city' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Bandung', 'code' => 'BDG', 'type' => 'city', 'city' => 'Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Yogyakarta', 'code' => 'YOG', 'type' => 'city', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
        ];
        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }

        // ── Moda Transportasi ──
        $rail = TransportMode::firstOrCreate(['code' => 'RAIL'], ['name' => 'Rail Cargo', 'code' => 'RAIL']);
        TransportMode::firstOrCreate(['code' => 'TRUCK'], ['name' => 'Trucking', 'code' => 'TRUCK', 'is_active' => false]);
        TransportMode::firstOrCreate(['code' => 'SEA'], ['name' => 'Sea Freight', 'code' => 'SEA', 'is_active' => false]);
        TransportMode::firstOrCreate(['code' => 'AIR'], ['name' => 'Air Cargo', 'code' => 'AIR', 'is_active' => false]);

        // ── Jenis Layanan (FCL / LCL) ──
        ServiceType::firstOrCreate(['code' => 'FCL'], [
            'transport_mode_id' => $rail->id, 'name' => 'Full Container Load', 'code' => 'FCL',
        ]);
        ServiceType::firstOrCreate(['code' => 'LCL'], [
            'transport_mode_id' => $rail->id, 'name' => 'Less Container Load', 'code' => 'LCL',
        ]);

        // ── Tipe Kontainer ──
        ContainerType::firstOrCreate(['size' => '20ft'], [
            'name' => 'Container 20ft', 'size' => '20ft',
            'capacity_weight' => 21770, 'capacity_cbm' => 33.2,
            'length' => 590, 'width' => 235, 'height' => 239,
        ]);
        ContainerType::firstOrCreate(['size' => '40ft'], [
            'name' => 'Container 40ft', 'size' => '40ft',
            'capacity_weight' => 26680, 'capacity_cbm' => 67.7,
            'length' => 1203, 'width' => 235, 'height' => 239,
        ]);

        // ── Layanan Tambahan ──
        $services = [
            ['name' => 'Pickup Service', 'category' => 'pickup', 'description' => 'Penjemputan barang dari alamat pengirim'],
            ['name' => 'Palletizing', 'category' => 'packing', 'description' => 'Pengemasan dengan pallet kayu'],
            ['name' => 'Wooden Crate', 'category' => 'packing', 'description' => 'Pengemasan dengan peti kayu'],
            ['name' => 'Bubble Wrap', 'category' => 'packing', 'description' => 'Pengemasan dengan bubble wrap'],
            ['name' => 'Reboxing', 'category' => 'packing', 'description' => 'Pengemasan ulang ke dalam box baru'],
            ['name' => 'Forklift Handling', 'category' => 'handling', 'description' => 'Penanganan menggunakan forklift'],
            ['name' => 'Fragile Handling', 'category' => 'handling', 'description' => 'Penanganan khusus barang mudah pecah'],
            ['name' => 'Heavy Cargo Handling', 'category' => 'handling', 'description' => 'Penanganan khusus kargo berat'],
        ];
        foreach ($services as $svc) {
            AdditionalService::firstOrCreate(['name' => $svc['name']], $svc);
        }
    }
}

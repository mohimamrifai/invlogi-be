<?php

namespace Database\Seeders;

use App\Models\AdditionalService;
use App\Models\ContainerType;
use App\Models\Location;
use App\Models\ServiceType;
use App\Models\TransportMode;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLocations();
        $this->seedTransportModes();
        $this->seedServiceTypes();
        $this->seedContainerTypes();
        $this->seedAdditionalServices();
    }

    private function seedLocations(): void
    {
        $rows = [
            ['name' => 'Jakarta', 'code' => 'JKT', 'type' => 'port', 'city' => 'Jakarta', 'province' => 'DKI Jakarta'],
            ['name' => 'Surabaya', 'code' => 'SUB', 'type' => 'port', 'city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Semarang', 'code' => 'SMG', 'type' => 'port', 'city' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Bandung', 'code' => 'BDG', 'type' => 'city', 'city' => 'Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Yogyakarta', 'code' => 'YOG', 'type' => 'city', 'city' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Medan', 'code' => 'MES', 'type' => 'port', 'city' => 'Medan', 'province' => 'Sumatera Utara'],
            ['name' => 'Belawan', 'code' => 'BLW', 'type' => 'port', 'city' => 'Medan', 'province' => 'Sumatera Utara'],
            ['name' => 'Palembang', 'code' => 'PLM', 'type' => 'port', 'city' => 'Palembang', 'province' => 'Sumatera Selatan'],
            ['name' => 'Batam', 'code' => 'BTH', 'type' => 'port', 'city' => 'Batam', 'province' => 'Kepulauan Riau'],
            ['name' => 'Pontianak', 'code' => 'PNK', 'type' => 'port', 'city' => 'Pontianak', 'province' => 'Kalimantan Barat'],
            ['name' => 'Balikpapan', 'code' => 'BPN', 'type' => 'port', 'city' => 'Balikpapan', 'province' => 'Kalimantan Timur'],
            ['name' => 'Samarinda', 'code' => 'SRI', 'type' => 'hub', 'city' => 'Samarinda', 'province' => 'Kalimantan Timur'],
            ['name' => 'Makassar', 'code' => 'MKS', 'type' => 'port', 'city' => 'Makassar', 'province' => 'Sulawesi Selatan'],
            ['name' => 'Manado', 'code' => 'MDC', 'type' => 'port', 'city' => 'Manado', 'province' => 'Sulawesi Utara'],
            ['name' => 'Denpasar', 'code' => 'DPS', 'type' => 'city', 'city' => 'Denpasar', 'province' => 'Bali'],
            ['name' => 'Malang', 'code' => 'MLG', 'type' => 'city', 'city' => 'Malang', 'province' => 'Jawa Timur'],
            ['name' => 'Cirebon', 'code' => 'CBN', 'type' => 'city', 'city' => 'Cirebon', 'province' => 'Jawa Barat'],
            ['name' => 'Solo', 'code' => 'SOC', 'type' => 'city', 'city' => 'Surakarta', 'province' => 'Jawa Tengah'],
            ['name' => 'Pekanbaru', 'code' => 'PKU', 'type' => 'city', 'city' => 'Pekanbaru', 'province' => 'Riau'],
            ['name' => 'Padang', 'code' => 'PDG', 'type' => 'port', 'city' => 'Padang', 'province' => 'Sumatera Barat'],
            ['name' => 'Bandar Lampung', 'code' => 'TKG', 'type' => 'port', 'city' => 'Bandar Lampung', 'province' => 'Lampung'],
            ['name' => 'Banjarmasin', 'code' => 'BDJ', 'type' => 'port', 'city' => 'Banjarmasin', 'province' => 'Kalimantan Selatan'],
            ['name' => 'Hub Cikarang', 'code' => 'CKG', 'type' => 'hub', 'city' => 'Cikarang', 'province' => 'Jawa Barat'],
            ['name' => 'Gudang BSD', 'code' => 'BSD', 'type' => 'warehouse', 'city' => 'Tangerang Selatan', 'province' => 'Banten'],
            ['name' => 'Gudang Karawang', 'code' => 'KWG', 'type' => 'warehouse', 'city' => 'Karawang', 'province' => 'Jawa Barat'],
        ];

        foreach ($rows as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }
    }

    private function seedTransportModes(): void
    {
        $rows = [
            ['name' => 'Rail Cargo', 'code' => 'RAIL', 'is_active' => true],
            ['name' => 'Trucking', 'code' => 'TRUCK', 'is_active' => true],
            ['name' => 'Sea Freight', 'code' => 'SEA', 'is_active' => true],
            ['name' => 'Air Cargo', 'code' => 'AIR', 'is_active' => true],
            ['name' => 'Multimodal', 'code' => 'MULTI', 'is_active' => true],
            ['name' => 'Barge', 'code' => 'BRG', 'is_active' => true],
            ['name' => 'Feeder Vessel', 'code' => 'FDR', 'is_active' => true],
            ['name' => 'Mother Vessel', 'code' => 'MVS', 'is_active' => true],
            ['name' => 'Express Truck', 'code' => 'XTRK', 'is_active' => true],
            ['name' => 'Cold Chain Truck', 'code' => 'CCT', 'is_active' => true],
            ['name' => 'Charter Air', 'code' => 'CHRT', 'is_active' => false],
            ['name' => 'Last Mile', 'code' => 'LML', 'is_active' => true],
            ['name' => 'Cross Border Truck', 'code' => 'CBT', 'is_active' => true],
            ['name' => 'RoRo', 'code' => 'RORO', 'is_active' => true],
            ['name' => 'Coastal Shipping', 'code' => 'CST', 'is_active' => true],
            ['name' => 'Inland Waterway', 'code' => 'IWW', 'is_active' => false],
            ['name' => 'Pipeline Segment', 'code' => 'PLN', 'is_active' => false],
            ['name' => 'Drone Pilot (trial)', 'code' => 'DRN', 'is_active' => false],
            ['name' => 'Hub Shuttle', 'code' => 'HUB', 'is_active' => true],
            ['name' => 'Dedicated Train Block', 'code' => 'DTB', 'is_active' => true],
            ['name' => 'Project Cargo', 'code' => 'PRJ', 'is_active' => true],
            ['name' => 'Heavy Lift', 'code' => 'HVY', 'is_active' => true],
            ['name' => 'Breakbulk Vessel', 'code' => 'BBK', 'is_active' => true],
            ['name' => 'Courier Integrasi', 'code' => 'CRR', 'is_active' => true],
            ['name' => 'Depot Transfer', 'code' => 'DPT', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            TransportMode::firstOrCreate(['code' => $row['code']], $row);
        }
    }

    private function seedServiceTypes(): void
    {
        $modes = TransportMode::orderBy('id')->get()->keyBy('code');
        $rail = $modes['RAIL'] ?? TransportMode::first();

        $definitions = [
            ['code' => 'FCL', 'name' => 'Full Container Load', 'mode' => 'RAIL'],
            ['code' => 'LCL', 'name' => 'Less Container Load', 'mode' => 'RAIL'],
            ['code' => 'FCL_S', 'name' => 'FCL Sea Standard', 'mode' => 'SEA'],
            ['code' => 'LCL_S', 'name' => 'LCL Sea Consolidation', 'mode' => 'SEA'],
            ['code' => 'AIR_STD', 'name' => 'Air Standard', 'mode' => 'AIR'],
            ['code' => 'AIR_EXP', 'name' => 'Air Express', 'mode' => 'AIR'],
            ['code' => 'FTL', 'name' => 'Full Truck Load', 'mode' => 'TRUCK'],
            ['code' => 'LTL', 'name' => 'Less Truck Load', 'mode' => 'TRUCK'],
            ['code' => 'IM4', 'name' => 'Intermodal 4PL', 'mode' => 'MULTI'],
            ['code' => 'BRG_STD', 'name' => 'Barge Standard', 'mode' => 'BRG'],
            ['code' => 'FDR_CON', 'name' => 'Feeder Connect', 'mode' => 'FDR'],
            ['code' => 'MVS_LIN', 'name' => 'Mainline Ocean', 'mode' => 'MVS'],
            ['code' => 'XTRK_EXP', 'name' => 'Express Road', 'mode' => 'XTRK'],
            ['code' => 'CCT_TMP', 'name' => 'Reefer Trucking', 'mode' => 'CCT'],
            ['code' => 'LML_PAR', 'name' => 'Parcel Last Mile', 'mode' => 'LML'],
            ['code' => 'CBT_XB', 'name' => 'Cross Border FTL', 'mode' => 'CBT'],
            ['code' => 'RORO_V', 'name' => 'RoRo Vehicle', 'mode' => 'RORO'],
            ['code' => 'CST_ISL', 'name' => 'Island Coastal', 'mode' => 'CST'],
            ['code' => 'HUB_XD', 'name' => 'Hub Cross-Dock', 'mode' => 'HUB'],
            ['code' => 'DTB_BLK', 'name' => 'Dedicated Block Train', 'mode' => 'DTB'],
            ['code' => 'PRJ_ODC', 'name' => 'ODC Project', 'mode' => 'PRJ'],
            ['code' => 'HVY_LFT', 'name' => 'Heavy Lift Package', 'mode' => 'HVY'],
            ['code' => 'BBK_STD', 'name' => 'Breakbulk Standard', 'mode' => 'BBK'],
            ['code' => 'CRR_DOM', 'name' => 'Domestic Courier', 'mode' => 'CRR'],
            ['code' => 'DPT_SHF', 'name' => 'Depot Shift', 'mode' => 'DPT'],
            ['code' => 'CHRT_VIP', 'name' => 'Charter Flight VIP', 'mode' => 'CHRT'],
            ['code' => 'IWW_BAR', 'name' => 'Inland Waterway Barge', 'mode' => 'IWW'],
            ['code' => 'PLN_BATCH', 'name' => 'Pipeline Batch Segment', 'mode' => 'PLN'],
            ['code' => 'DRN_TRIAL', 'name' => 'Drone Trial Delivery', 'mode' => 'DRN'],
        ];

        foreach ($definitions as $def) {
            $mode = $modes[$def['mode']] ?? $rail;
            ServiceType::firstOrCreate(
                ['code' => $def['code']],
                [
                    'transport_mode_id' => $mode->id,
                    'name' => $def['name'],
                    'description' => 'Seeded service type '.$def['code'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedContainerTypes(): void
    {
        $rows = [
            ['name' => 'Container 20ft Standard', 'size' => '20ft', 'capacity_weight' => 21770, 'capacity_cbm' => 33.2, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => 'Container 40ft Standard', 'size' => '40ft', 'capacity_weight' => 26680, 'capacity_cbm' => 67.7, 'length' => 1203, 'width' => 235, 'height' => 239],
            ['name' => '40ft High Cube', 'size' => '40HC', 'capacity_weight' => 26500, 'capacity_cbm' => 76.3, 'length' => 1203, 'width' => 235, 'height' => 269],
            ['name' => '45ft High Cube', 'size' => '45HC', 'capacity_weight' => 27800, 'capacity_cbm' => 86.0, 'length' => 1356, 'width' => 235, 'height' => 269],
            ['name' => '20ft Open Top', 'size' => '20OT', 'capacity_weight' => 21000, 'capacity_cbm' => 32.5, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '40ft Open Top', 'size' => '40OT', 'capacity_weight' => 26000, 'capacity_cbm' => 65.0, 'length' => 1203, 'width' => 235, 'height' => 239],
            ['name' => '20ft Flat Rack', 'size' => '20FR', 'capacity_weight' => 28000, 'capacity_cbm' => 28.0, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '40ft Flat Rack', 'size' => '40FR', 'capacity_weight' => 40000, 'capacity_cbm' => 52.0, 'length' => 1203, 'width' => 235, 'height' => 239],
            ['name' => '20ft Reefer', 'size' => '20RF', 'capacity_weight' => 20400, 'capacity_cbm' => 28.3, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '40ft Reefer', 'size' => '40RF', 'capacity_weight' => 25000, 'capacity_cbm' => 67.3, 'length' => 1203, 'width' => 235, 'height' => 269],
            ['name' => '20ft Tank', 'size' => '20TK', 'capacity_weight' => 24000, 'capacity_cbm' => 24.0, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '10ft Mini', 'size' => '10ft', 'capacity_weight' => 10160, 'capacity_cbm' => 15.9, 'length' => 299, 'width' => 235, 'height' => 239],
            ['name' => '53ft Domestic', 'size' => '53ft', 'capacity_weight' => 20000, 'capacity_cbm' => 110.0, 'length' => 1610, 'width' => 244, 'height' => 274],
            ['name' => '20ft Hard Top', 'size' => '20HT', 'capacity_weight' => 21500, 'capacity_cbm' => 31.0, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '40ft Hard Top', 'size' => '40HT', 'capacity_weight' => 26200, 'capacity_cbm' => 64.0, 'length' => 1203, 'width' => 235, 'height' => 239],
            ['name' => '20ft Platform', 'size' => '20PL', 'capacity_weight' => 24000, 'capacity_cbm' => 0, 'length' => 590, 'width' => 235, 'height' => 50],
            ['name' => '40ft Platform', 'size' => '40PL', 'capacity_weight' => 40000, 'capacity_cbm' => 0, 'length' => 1203, 'width' => 235, 'height' => 50],
            ['name' => '20ft Bulk', 'size' => '20BK', 'capacity_weight' => 25000, 'capacity_cbm' => 35.0, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => 'Air ULD LD3', 'size' => 'LD3', 'capacity_weight' => 1588, 'capacity_cbm' => 4.3, 'length' => 156, 'width' => 153, 'height' => 163],
            ['name' => 'Air ULD LD7', 'size' => 'LD7', 'capacity_weight' => 3500, 'capacity_cbm' => 10.5, 'length' => 318, 'width' => 244, 'height' => 163],
            ['name' => 'Air ULD PMC', 'size' => 'PMC', 'capacity_weight' => 6800, 'capacity_cbm' => 17.5, 'length' => 318, 'width' => 244, 'height' => 163],
            ['name' => 'Half Height 20ft', 'size' => '20HH', 'capacity_weight' => 28000, 'capacity_cbm' => 14.0, 'length' => 590, 'width' => 235, 'height' => 145],
            ['name' => 'Garmentainer 40ft', 'size' => '40GOH', 'capacity_weight' => 22000, 'capacity_cbm' => 67.0, 'length' => 1203, 'width' => 235, 'height' => 269],
            ['name' => '20ft Side Door', 'size' => '20SD', 'capacity_weight' => 21000, 'capacity_cbm' => 32.0, 'length' => 590, 'width' => 235, 'height' => 239],
            ['name' => '40ft Double Door', 'size' => '40DD', 'capacity_weight' => 25800, 'capacity_cbm' => 67.0, 'length' => 1203, 'width' => 235, 'height' => 239],
        ];

        foreach ($rows as $row) {
            ContainerType::firstOrCreate(['size' => $row['size']], array_merge($row, ['is_active' => true]));
        }
    }

    private function seedAdditionalServices(): void
    {
        $rows = [
            ['name' => 'Pickup Service', 'category' => 'pickup', 'description' => 'Penjemputan barang dari alamat pengirim', 'base_price' => 350000],
            ['name' => 'Pickup Bandara', 'category' => 'pickup', 'description' => 'Pickup kargo dari area bandara', 'base_price' => 500000],
            ['name' => 'Pickup Pelabuhan', 'category' => 'pickup', 'description' => 'Pickup kontainer dari pelabuhan', 'base_price' => 750000],
            ['name' => 'Pickup Malam', 'category' => 'pickup', 'description' => 'Pickup di luar jam kerja', 'base_price' => 450000],
            ['name' => 'Palletizing', 'category' => 'packing', 'description' => 'Pengemasan dengan pallet kayu', 'base_price' => 200000],
            ['name' => 'Wooden Crate', 'category' => 'packing', 'description' => 'Pengemasan dengan peti kayu', 'base_price' => 450000],
            ['name' => 'Bubble Wrap', 'category' => 'packing', 'description' => 'Pelindung bubble wrap', 'base_price' => 75000],
            ['name' => 'Reboxing', 'category' => 'packing', 'description' => 'Pengemasan ulang ke box baru', 'base_price' => 125000],
            ['name' => 'Vacuum Pack', 'category' => 'packing', 'description' => 'Vacuum packaging untuk tekstil', 'base_price' => 180000],
            ['name' => 'Shrink Wrap', 'category' => 'packing', 'description' => 'Shrink wrap palet', 'base_price' => 95000],
            ['name' => 'ISPM15 Treatment', 'category' => 'packing', 'description' => 'Treatment kayu ekspor', 'base_price' => 275000],
            ['name' => 'Forklift Handling', 'category' => 'handling', 'description' => 'Penanganan menggunakan forklift', 'base_price' => 300000],
            ['name' => 'Fragile Handling', 'category' => 'handling', 'description' => 'Penanganan barang mudah pecah', 'base_price' => 250000],
            ['name' => 'Heavy Cargo Handling', 'category' => 'handling', 'description' => 'Penanganan kargo berat', 'base_price' => 600000],
            ['name' => 'Crane Lift', 'category' => 'handling', 'description' => 'Angkat dengan mobile crane', 'base_price' => 2500000],
            ['name' => 'Sortir Gudang', 'category' => 'handling', 'description' => 'Sortir dan staging barang', 'base_price' => 175000],
            ['name' => 'Labeling & Marking', 'category' => 'other', 'description' => 'Label SSCC dan marking', 'base_price' => 85000],
            ['name' => 'Dokumen Customs', 'category' => 'other', 'description' => 'Bantuan dokumen kepabeanan', 'base_price' => 400000],
            ['name' => 'Asuransi Kargo Dasar', 'category' => 'other', 'description' => 'Asuransi dasar per invoice', 'base_price' => 150000],
            ['name' => 'Fumigasi', 'category' => 'other', 'description' => 'Fumigasi kontainer', 'base_price' => 900000],
            ['name' => 'Storage 3 Hari', 'category' => 'other', 'description' => 'Penyimpanan sementara 3 hari', 'base_price' => 500000],
            ['name' => 'Chiller Handling', 'category' => 'handling', 'description' => 'Handling suhu terkendali', 'base_price' => 550000],
            ['name' => 'Oversized Escort', 'category' => 'other', 'description' => 'Escort muatan ODOL', 'base_price' => 3200000],
            ['name' => 'Cargo Survey', 'category' => 'other', 'description' => 'Survey sebelum pengiriman', 'base_price' => 650000],
            ['name' => 'Detention Monitoring', 'category' => 'other', 'description' => 'Monitoring demurrage/detention', 'base_price' => 200000],
        ];

        foreach ($rows as $svc) {
            AdditionalService::firstOrCreate(['name' => $svc['name']], $svc);
        }
    }
}

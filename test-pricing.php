<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sell = \App\Models\Pricing::where('price_type', 'sell')->count();
$buy = \App\Models\Pricing::where('price_type', 'buy')->count();
echo "Sell: $sell, Buy: $buy\n";

$vendorServices = \App\Models\VendorService::count();
echo "Vendor services: $vendorServices\n";

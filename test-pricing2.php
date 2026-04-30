<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sellWithContainer = \App\Models\Pricing::where('price_type', 'sell')->whereNotNull('container_type_id')->count();
$sellWithoutContainer = \App\Models\Pricing::where('price_type', 'sell')->whereNull('container_type_id')->count();

echo "Sell w/ container: $sellWithContainer, w/o container: $sellWithoutContainer\n";

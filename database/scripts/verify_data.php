<?php
$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domain\Region\Models\Province;
use App\Domain\Region\Models\Regency;
use App\Domain\Region\Models\District;
use App\Domain\Region\Models\Village;

echo "=== Data Counts ===" . PHP_EOL;
echo "Provinces: " . Province::count() . PHP_EOL;
echo "Regencies: " . Regency::count() . PHP_EOL;
echo "Districts: " . District::count() . PHP_EOL;
echo "Villages:  " . Village::count() . PHP_EOL;

echo PHP_EOL . "=== Bojonegoro Districts ===" . PHP_EOL;
$districts = District::where('regency_code', '35.22')->get(['code', 'name']);
foreach ($districts as $d) {
    echo "  " . $d->code . " - " . $d->name . PHP_EOL;
}

echo PHP_EOL . "=== Bojonegoro Villages (first 10) ===" . PHP_EOL;
$villages = Village::whereIn('district_code', $districts->pluck('code'))->take(10)->get(['code', 'name', 'district_code']);
foreach ($villages as $v) {
    echo "  " . $v->code . " - " . $v->name . " (district: " . $v->district_code . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Sample from other provinces ===" . PHP_EOL;
$sample = District::where('regency_code', '11.01')->take(3)->get(['code', 'name']);
foreach ($sample as $d) {
    echo "  " . $d->code . " - " . $d->name . PHP_EOL;
}

<?php
$d = json_decode(file_get_contents('database/data/districts.json'), true);
$bjn = array_filter($d, function ($r) {
    return $r['regency_code'] === '35.22'; });
foreach ($bjn as $r) {
    echo "['regency_code' => '35.22', 'code' => '" . $r['code'] . "', 'name' => '" . $r['name'] . "'],\n";
}
echo "\n--- Villages ---\n";
$v = json_decode(file_get_contents('database/data/villages.json'), true);
$bjnDistricts = array_column(array_values($bjn), 'code');
$bjnVillages = array_filter($v, function ($r) use ($bjnDistricts) {
    return in_array($r['district_code'], $bjnDistricts);
});
// Group by district
$grouped = [];
foreach ($bjnVillages as $village) {
    $grouped[$village['district_code']][] = $village;
}
foreach ($grouped as $distCode => $villages) {
    $distName = '';
    foreach ($bjn as $dd) {
        if ($dd['code'] === $distCode) {
            $distName = $dd['name'];
            break;
        }
    }
    echo "\n// {$distName} ({$distCode})\n";
    foreach ($villages as $vv) {
        echo "['district_code' => '{$vv['district_code']}', 'code' => '{$vv['code']}', 'name' => '{$vv['name']}'],\n";
    }
}

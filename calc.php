<?php

$items = [];
$json = file_get_contents('tmp/_metaData.json');
$items = json_decode($json, true);

$otoAnimes = [];
foreach($items as $item) {
    if (strpos($item['name'], 'Oto') === 0) {
        $otoAnimes[] = $item;
    }
}

$attributeMap = [];
foreach($otoAnimes as $otoAnime) {
    foreach($otoAnime['attributes'] as $attr => $value) {
        $attributeMap[$attr][$value] = isset($attributeMap[$attr][$value]) ? $attributeMap[$attr][$value] + 1 : 1;
    }
}

foreach($otoAnimes as &$otoAnime) {
    $otoAnime['weight'] = 0;
    foreach($otoAnime['attributes'] as $attr => $value) {
        $otoAnime['weight'] += $attributeMap[$attr][$value] / 2222;
    }
}
$weights = array_column($otoAnimes, 'weight');
array_multisort($weights, SORT_DESC, $otoAnimes);
for($i = 0; $i < 55; ++$i) {
    print('token_id,' . $otoAnimes[$i]['edition'] . "\n");
}


<?php

$config = require_once('./config.php');
require_once('./class/MetaData.php');

$rawData = file_get_contents($config['metadata_dir'] . '/' . MetaData::JSON_FILE_NAME);
if ($rawData) {
    $items = json_decode($rawData, true);
    $metaData = new MetaData($config['metadata_dir']);
    foreach ($items as $item) {
        $item['name'] = "{$config['name_prefix']}{$config['name_spacer']}{$item['edition']}";
        $item['description'] = $config['description'];
        $item['image'] = "{$config['base_uri']}/{$item['edition']}.gif";
        $metaData->writeItemAndAdd($item);
    }
    $metaData->writeJsonMetaData();
    echo "success!\n";
} else {
    echo "metadata does not exist!\n";
}

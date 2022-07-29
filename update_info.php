<?php

$config = require_once('./config.php');
require_once('./class/MetaData.php');

$rawData = file_get_contents($config['metadata_dir'] . '/' . MetaData::JSON_FILE_NAME);
if ($rawData) {
    $rows = json_decode($rawData, true);
    $metaData = new MetaData($config['metadata_dir']);
    foreach ($rows as $row) {
        $row['name'] = "{$config['name_prefix']}{$config['name_spacer']}{$row['edition']}";
        $row['description'] = $config['description'];
        $row['image'] = "{$config['base_uri']}/{$row['edition']}.gif";
        $metaData->writeRowAndAdd($row);
    }
    $metaData->writeJsonMetaData();
    echo "success!\n";
} else {
    echo "metadata does not exist!\n";
}

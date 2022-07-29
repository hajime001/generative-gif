<?php

$config = require_once('./config.php');
require_once('./class/MetaData.php');

$metaData = new MetaData($config['metadata_dir']);
$metaData->loadCsvMetaData();
$metaData->writeJsonMetaData();
echo "success!\n";

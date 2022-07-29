<?php

$config = require_once('./config.php');
require_once('./class/MetaData.php');

$metaData = new MetaData($config['metadata_dir']);
$metaData->loadJsonMetaData();
$metaData->writeCsvMetaData();
echo "success!\n";

<?php

$configs = require_once('./config.php');
require_once('./class/MetaData.php');

$metaData = new MetaData($configs[4]['metadata_dir']);
$metaData->loadJsonMetaData();
$metaData->convert();
$metaData->writeJsonMetaData();
$metaData->writeCsvMetaData();
echo "success!\n";

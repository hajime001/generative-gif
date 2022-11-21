<?php

$configs = require_once('./config.php');
require_once('./class/ImageMixer.php');

ini_set('memory_limit', '8192M');

$imageMixer = new ImageMixer($configs[$argv[1]]);
$start = $argv[2];
$end = $argv[3];
$imageMixer->execute(true, $start, $end);

foreach($configs as $config) {
//    $imageMixer = new ImageMixer($config);
//    $imageMixer->execute(true);
}
echo "success!\n";

<?php

$configs = require_once('./config.php');
require_once('./class/ImageMixer.php');

ini_set('memory_limit', '8192M');

$imageMixer = new ImageMixer($configs[$argv[1]]);
$imageMixer->execute(true);


foreach($configs as $config) {
//    $imageMixer = new ImageMixer($config);
//    $imageMixer->execute(true);
}
echo "success!\n";

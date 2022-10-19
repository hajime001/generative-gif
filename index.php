<?php

$configs = require_once('./config.php');
require_once('./class/ImageMixer.php');

ini_set('memory_limit', '8192M');

foreach($configs as $config) {
    $imageMixer = new ImageMixer($config);
    $imageMixer->execute();
}
echo "success!\n";
